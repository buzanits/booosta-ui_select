<?php
namespace booosta\ui_select;

\booosta\Framework::add_module_trait('webapp', 'ui_select\webapp');

trait webapp
{
  protected function preparse_ui_select()
  {
    if($this->moduleinfo['ui_select']):
      $this->add_includes("<script type='text/javascript' src='vendor/booosta/ui_select/selectize.min.js'></script>
<link rel='stylesheet' type='text/css' href='{$this->base_dir}lib/modules/ui_select/selectize.default.css' media='screen' />");
    endif;
  }
}
