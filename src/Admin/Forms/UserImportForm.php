<?php

namespace Porygon\User\Admin\Forms;

use Dcat\Admin\Widgets\Form;
use Illuminate\Support\Facades\Storage;
use Porygon\User\Jobs\ImportUserExcel;

class UserImportForm extends Form
{

    /**
     * Handle the form request.
     *
     * @param array $input
     *
     * @return mixed
     */
    public function handle(array $input)
    {
        $disk = Storage::disk(config('admin.upload.disk'));
        if (isset($input['excel']) && $disk->exists($input['excel'])) {
            ImportUserExcel::dispatch($input['excel']);
            return $this->response()->success('正在后台导入')->refresh();
        } else {
            return $this->response()->error('请确认上传文件是否成功！');
        }
    }

    /**
     * Build a form here.
     */
    public function form()
    {
        $this->file('excel', "文件")->required()->rules('mimes:xlsx')->uniqueName()->autoUpload();
        // $this->button("下载模板");
        $route = admin_route("user.users.import.template");
        $this->html('<button class="btn btn-sm pull-right"><a href="' . $route . '" target="_blank">下载模板</a></button>');
    }

    /**
     * The data of the form.
     *
     * @return array
     */
    public function default()
    {
        return [];
    }
}
