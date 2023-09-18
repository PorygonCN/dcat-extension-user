<?php

namespace Porygon\User\Jobs;

use Dcat\Admin\Models\Extension;
use Porygon\Organization\Models\Department;
use Dcat\EasyExcel\Excel;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class ImportUserExcel implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $path;
    public $departments;
    public $models;
    public $hasDepartment = false;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($path)
    {
        $this->path        = $path;
        $this->departments = collect();
        $this->models["user"] = config("juser.models.user", User::class);
        if (config("juser.user.has_department", false)) {
            $this->models["department"] = config("juser.models.department", Department::class);
        }
        if (Extension::whereName("porygon.organization")->whereIsEnabled(true)->exists()) {
            $this->hasDepartment = true;
        }
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $errors = DB::transaction(function () {
            $headings  = ["no", "name", 'username', 'phone', 'email', 'password', 'enable', 'department'];
            $allSheets = Excel::import($this->path)->disk(config('admin.upload.disk'))->headings($headings)->toArray();
            $users     = [];
            $errors    = [];
            foreach ($allSheets as $sheet) {
                foreach ($sheet as $row) {
                    $department = $row["department"];
                    $item       = $row;
                    unset($item['department']);
                    $item['enable']        = $item['enable'] == '是' ? true : false;
                    $item["password"]      = empty($item["password"]) ? Hash::make("123456a?") : Hash::make($item["password"]);
                    $this->hasDepartment   && $item["department_id"] = $this->getDepartmentId($department);
                    $users[]               = $item;
                }
            }
            foreach ($users as $user) {
                try {
                    $this->models["user"]::create($user);
                } catch (Exception $e) {
                    $errors[] = ["message" => $e->getMessage(), "user" => $user];
                }
            }
            return $errors;
        });
        if ($errors) {
            # 把错误存下来 整个错误查看
            throw new Exception($errors);
        }
    }

    /**
     * 获取部门信息
     */
    public function getDepartmentId($department)
    {
        $department_arr = explode("/", $department);
        $department     = null;
        foreach ($department_arr as $item) {
            $builder = $this->departments;
            $current = $builder->where("title", $item)->first();
            if (!$current) {
                $builder    = $department ? $department->departments() : Department::query();
                $current    = $builder->where("title", $item)->first();
                $department = $current ?? Department::create(["title" => $item, "parent_id" => $department ? $department->id : 0]);
            }
            $department = $current;
        }
        return $department?->id;
    }
}
