<?php
namespace booosta\ui_select;
\booosta\Framework::init_module('ui_select');

class UI_Select extends \booosta\ui\UI
{
  use moduletrait_ui_select;

  protected $select;
  protected $onchange;
  protected $type = 'select';
  protected $width = '100%';
  protected $placeholder;
  protected $extra;
  protected $is_grouped = false;
  protected $subselect, $subselect_url;
  protected $nosort = false;
  protected $ajaxload = false;


  public function __construct($name = null, $options = null, $default = null, $caption = null, $size = null, $multiple = null)
  {
    parent::__construct();
    $this->id = "ui_select_$name";
    $this->placeholder = $caption;
    $this->extra = '';
    if(is_array($options)) $this->is_grouped = $this->is_multidim($options);

    $this->construct = ['name' => $name, 'options' => $options, 'default' => $default, 'caption' => $caption, 'size' => $size,
                        'multiple' => $multiple];
  }

  public function after_instanciation()
  {
    parent::after_instanciation();

    $this->select = $this->makeInstance("\\booosta\\formelements\\Select", $this->construct['name'], $this->construct['options'], 
                                        $this->construct['default'], null, $this->construct['size'], $this->construct['multiple']);
    $this->select->set_id("ui_select_{$this->construct['name']}");
    unset($this->construct);

    if(is_object($this->topobj) && is_a($this->topobj, "\\booosta\\webapp\\Webapp")):
      $this->topobj->moduleinfo['ui_select'] = true;
      if($this->topobj->moduleinfo['jquery']['use'] == '') $this->topobj->moduleinfo['jquery']['use'] = true;
    endif;
  }

  public function onchange($code) { $this->onchange = $code; }
  public function set_type($type) { $this->type = $type; }
  public function set_width($width) { $this->width = $width; }
  public function set_placeholder($placeholder) { $this->placeholder = $placeholder; }
  public function add_extra($code) { $this->extra .= $code; }
  public function set_nosort($flag) { $this->nosort = $flag; }
  public function set_ajaxload($flag) { $this->ajaxload = $flag; }


  public function set_subselect($sub_id, $url)
  {
    $this->subselect = $sub_id;
    $this->subselect_url = $url;
  }

  public function get_htmlonly() { 
    if($this->type == 'tags') $this->select->set_multiple(true);
    if($this->placeholder) $this->select->add_extra_attr("placeholder='$this->placeholder'");
    if($this->width) $this->select->add_extra_attr("style='width: $this->width'");

    #\booosta\debug($this->select->get_html());
    return $this->select->get_html(); 
  }

  public function get_js()
  {
    if($this->subselect):
      $subselectid = "ui_select_$this->subselect";
      $url = $this->subselect_url ? $this->subselect_url : "?action=get_$subselectid&param=value";

      $subselect = "<script type='text/javascript'> 
      function load_ui_select_$this->subselect(value) {
        $subselectid.disable();
        $subselectid.clearOptions();
        $subselectid.load(function(callback) {
          var xhr; xhr && xhr.abort();
          xhr = $.ajax({
            url: $url,
            success: function(results) { $subselectid.enable(); callback(results); },
            error: function() { callback(); }
          }) }); }</script>";

      $this->onchange .= "load_$subselectid(value);";
    endif;

    if($this->onchange) $extra = "onChange: function(value) { $this->onchange; }, ";
    if($this->type == 'tags') $extra .= "plugins: ['remove_button'],";
    $extra .= "valueField: 'id', labelField: 'name', searchField: 'name', ";

    if($this->is_grouped):
      $extra .= "optgroupField: 'optgf', ";
      $sortstr = "{field: 'optgf'}, {field: 'name'}";
    else:
      $sortstr = "{field: 'name'}";
    endif;

    if(!$this->nosort)  $extra .= "sortField: [$sortstr], ";
    $extra .= $this->extra;

    // use this until the selectize bug is fixed where you need to press backspace to search in empty field
    $extra .= 'onDropdownOpen: function () { var self = this; var value = self.getValue(); value = value || "";
               if (!value.length || value == "0") { self.clear(); return; }}, ';

    $code = "if(\$('#$this->id').length) {
               \$$this->id = \$('#$this->id').selectize({ persist: false, createOnBlur: true, $extra create: false }); $this->id  = \$$this->id[0].selectize; } ";

    if($this->ajaxload):
      $code .= "function {$this->id}_ajaxload(ajaxurl, defaultval) {
                $this->id.disable(); $this->id.clearOptions(); $this->id.load(function(callback) {
                var xhr; xhr && xhr.abort(); xhr = $.ajax({ url: ajaxurl,
                success: function(results) { $this->id.enable(); callback(results); $this->id.setValue(defaultval); },
                error: function() { callback(); } }) }); } ";
    endif;
    
    if(is_object($this->topobj) && is_a($this->topobj, '\booosta\webapp\webapp')):
      $this->topobj->add_jquery_ready($code);
      if($subselect) $this->topobj->add_includes($subselect);
      return '';
    else:
      return "$subselect \$(document).ready(function(){ $code });";
    endif;
  }

  public function __call($name, $args)
  {
    return call_user_func_array([$this->select, $name], $args);
  }
  
  protected function is_multidim($opts) { return is_array(current($opts)); }

  public static function print_response($data)
  {
    header('Content-Type: application/json; charset=utf-8');
    $opts = [];

    foreach($data as $key=>$value)
      if(is_array($value)) $opts[] = ['id' => $key, 'name' => $value['name'], 'optgf' => $value['group']];
      else $opts[] = ['id' => $key, 'name' => $value];

    print json_encode($opts);
  }
}
