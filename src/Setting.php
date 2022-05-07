<?php

namespace Porygon\User;

use Dcat\Admin\Extend\Setting as Form;
use Dcat\Admin\Models\Extension;

class Setting extends Form
{
    public function form()
    {
        // $this->switch('mast_email_verify')->default(false);
        $this->switch('has_organization', "组织架构")->default(false)->display(Extension::whereName("porygon.organization")->whereIsEnabled(true)->exists());
    }
}
