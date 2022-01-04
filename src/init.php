<?php
namespace booosta\ui_select;

\booosta\Framework::add_module_trait('webapp', 'ui_select\webapp');

trait webapp
{
  protected function preparse_ui_select()
  {
    if($this->moduleinfo['ui_select']):
      $this->add_includes("<script type='text/javascript' src='vendor/grimmlink/selectize/dist/js/standalone/selectize.js'></script>
<link rel='stylesheet' type='text/css' href='vendor/grimmlink/selectize/dist/css/selectize.default.css' media='screen' />");
    endif;
  }
}
