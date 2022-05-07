<?php

namespace Porygon\User\Admin\Renderable;

use Porygon\Organization\Models\Department;
use Dcat\Admin\Grid;
use Dcat\Admin\Grid\LazyRenderable;
use Porygon\User\Admin\Repositories\User;

class UserTable extends LazyRenderable
{
    public function grid(): Grid
    {
        // 获取外部传递的参数
        return Grid::make(User::with("department"), function (Grid $grid) {
            $grid->model()->where("enable", true);
            // $grid->column('id');
            $grid->column('no', "工号");
            $grid->column('name');
            $grid->column('username');
            $grid->column('department_id', "部门")->display(function () {
                return $this->department?->title;
            });
            // $grid->column('created_at');
            // $grid->column('updated_at');

            $grid->quickSearch(['id', 'username', 'name']);

            // $grid->paginate(10);
            $grid->disableActions();

            $grid->filter(function (Grid\Filter $filter) {
                $filter->equal('department_id', "部门")->multipleSelect(Department::selectOptions(function ($builder) {
                    return  $builder->where("enable", true);
                }))->width(4);
                $filter->like('no', "工号")->width(4);
                $filter->like('name')->width(4);
                $filter->like('username')->width(4);
            });
        });
    }
}
