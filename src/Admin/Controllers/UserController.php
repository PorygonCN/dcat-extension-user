<?php

namespace Porygon\User\Admin\Controllers;

use Dcat\Admin\Admin;
use Dcat\Admin\Form;
use Dcat\Admin\Form\EmbeddedForm;
use Dcat\Admin\Form\NestedForm;
use Dcat\Admin\Grid;
use Dcat\Admin\Grid\Displayers\Actions;
use Dcat\Admin\Grid\Displayers\Expand;
use Dcat\Admin\Grid\Tools;
use Dcat\Admin\Show;
use Dcat\Admin\Http\Controllers\AdminController;
use Dcat\Admin\Models\Extension;
use Dcat\Admin\Widgets\Alert;
use Dcat\Admin\Widgets\Card;
use Dcat\Admin\Widgets\Modal;
use Dcat\Admin\Widgets\Table;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Porygon\Base\Exceptions\MsgableException;
use Porygon\Organization\Models\Department;
use Porygon\User\Admin\Forms\UserImportForm;
use Porygon\User\Admin\Repositories\User;
use Porygon\User\Models\User as ModelsUser;
use Porygon\User\ServiceProvider;
use Porygon\Xls\Driver\Xlswriter;

class UserController extends AdminController
{
    protected $translation = "p-user::user";

    protected $hasOrganization = false;
    protected $repository;

    public function __construct()
    {
        $this->repository = new User;
        if (ServiceProvider::setting("has_organization")) {
            $this->hasOrganization = true;
            $this->repository      = User::with(["department", "in_charge"]);
        }
    }
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = Grid::make($this->repository, function (Grid $grid) {
            $grid->tools(function (Tools $tools) {
                $tools->append(Modal::make()->button("<button class='btn btn-info pull-right' style='margin-left:4px'>导入</button>")->body(UserImportForm::make()));
            });
            // 启用
            $grid->scrollbarX();
            $grid->enableDialogCreate();
            $grid->disableBatchDelete();
            $grid->disableDeleteButton();


            $grid->filter(function (Grid\Filter $filter) {
                $filter->equal('id');
                $filter->like('no');
                $filter->like('name');
                $filter->like('username');
                if ($this->hasOrganization) {
                    $filter->equal('department_id')->select(Department::selectOptions());
                }
            });

            $grid->column('no')->sortable();
            $grid->column('name');
            $grid->column('username');

            if ($this->hasOrganization) {
                $grid->column('department_id')->display(function () {
                    return $this->department?->full_title;
                })->sortable()->limit(30);
                $grid->column("posts")->if(function () {
                    return $this->in_charge->count();
                })->expand(function (Expand $expand) {
                    $expand->button("点击查看");
                    $rows = [];
                    $this->in_charge->map(function ($item) use (&$rows) {
                        $rows[] = [$item->department->full_title, $item->post?->title];
                    });
                    $content = Table::make(["部门", "职务"], $rows);
                    return "<div style='padding:10px 10px 0'>$content</div>";
                })->else()->display("无");
            }

            $grid->column('email');
            $grid->column('mobile');
            $grid->column('enable')->switch();
        });

        return $grid;
    }

    /**
     * Make a show builder.
     *
     * @param mixed $id
     *
     * @return Show
     */
    protected function detail($id)
    {
        return Show::make($id, new User(), function (Show $show) {
            $show->field('id');
            $show->field('profile_photo_path');
            $show->field('department_id');
            $show->field('name');
            $show->field('username');
            $show->field('no');
            $show->field('email');
            $show->field('mobile');
            $show->field('password');
            $show->field('change_password');
            $show->field('enable');
            $show->field('remember_token');
            $show->field('created_at');
            $show->field('updated_at');
        });
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        return Form::make(new User(), function (Form $form) {
            $form->row(function (Form\Row $row) {
                $row->width(6)->image('profile_photo_path')->autoUpload(true)->uniqueName()->autoSave(false);
                $row->width(6)->switch('enable')->default(true);
            });
            $form->row(function (Form\Row $row) {
                $row->width(6)->text('no')->required()->help("使用自动生成则会生成系统中已有最大工号的后一位工号");
                $row->width(6)->html("<button class='btn btn-primary create_no' style='margin-top:6px' type='button'>自动生成</button>", "帮助");
            });
            if ($this->hasOrganization) {

                $form->row(function (Form\Row $row) {
                    $row->select('department_id')->options(Department::selectOptions());
                });
            }
            $form->row(function (Form\Row $row) {
                $row->width(6)->text('name')->required();
                $row->width(6)->text('username')->required();
            });
            $form->row(function (Form\Row $row) {
                $row->width(6)->email('email');
                $row->width(6)->mobile('mobile');
            });
            $form->row(function (Form\Row $row) use ($form) {
                $column = $row->width(6)->password('password')->customFormat(function ($v) {
                    if ($v == $this->password) {
                        return;
                    }
                    return $v;
                });
                $form->isCreating() && $column->help("不填则默认密码为【123456a?】");
                $column = $row->width(6)->password('c_password', "确认密码")->same('password');
                $form->isCreating() && $column->help("若使用默认密码则不需要填写");
            });
            $form->saving(function (Form $form) {
                if (empty($form->password)) {
                    $form->password = $form->isCreating() ? Hash::make("123456a?") : $form->model()->password;
                }
            });

            $form->ignore(["c_password"]);

            Admin::js(admin_asset("/vendor/dcat-admin-extensions/porygon/user/js/pinyin-pro.min.js"));

            $route = admin_route("api.user.getleastno");

            Admin::script(<<<JS
const { pinyin } = pinyinPro;
let opt={
    toneType: 'none',
    mode:'surname'
};

let name      = $("input[name='name']");
let username  = $("input[name='username']");
let email     = $("input[name='email']");
let create_no = $(".create_no");
name = $(name[name.length-1])
// console.log(name)
let sync = function(){
    let val = name.val();
    // console.log(val)
    let pyv = pinyin(val,opt)
    // console.log(pyv)
    let tpyv = pyv.replace(/\s*/g,"")
    let ev   = 'sz'+tpyv+'@strongest.cn';
    let unv  = 'sz'+tpyv;
    username.val(unv);
    email.val(ev);
    // console.log(ev)
}
name.on('change',sync)
name.on('keyup',sync)

create_no.on('click',function () {
    let no = $("input[name='no']");
    $.ajax({
        url:'{$route}',
        success:function(response){
            if(response.status){
                no.val(response.data.no);
            }
        }
    });
})
JS);
        });
    }

    /**
     * 获取最新工号
     */
    public function getLeastNo(Request $request)
    {
        $max_no = ModelsUser::query()->max("no");
        $no = substr(strval($max_no + 1000001), 1, 6);

        return Admin::json(["no" => $no]);
    }

    /**
     * 获取用户导入模板
     */
    public function getImportTemplate()
    {
        $path = $this->getImportFilePath();
        return response()->download($path, '用户数据导入模板.xlsx');
    }
    public function getImportFilePath()
    {
        $fileName = $this->hasOrganization ? "user_with_organization_import_template.xlsx" : "user_import_template.xlsx";
        $path = public_path("/vendor/dcat-admin-extensions/porygon/user/template/$fileName");
        !is_file($path) &&  $path = base_path("/vendor/porygon/user/resource/template/$fileName");
        return $path;
    }
}
