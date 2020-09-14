<div id="form-multiple-add-container" class="hidden">
    <div class="form-multiple-add" style="display: block;">
        <h3 class="form-head">
            <div class="align-right">
                <span class="fh-delete">
                    <a href="javascript:void(0)" class="delete-detail">DELETE</a>
                </span>
            </div>
        </h3>
        <?php
            if (isset($fieldgroup['fields']) && sizeof($fieldgroup['fields']) > 0 && $fieldgroup['standard_custom_view'] == 1) :
                foreach ($fieldgroup['fields'] as $field) :
                    //rename fieldname in to array
                    $field['fieldname'] = strtolower($fieldgroup['fieldgroup_label']).'['.$field['fieldname'].']'.'[]';
                    //set js validation params
                    $datatypes = explode('~', $field['datatype']);
                    $is_mandatory = false;
                    $is_readonly = in_array( 'R', $datatypes )? true : false;
                    if( $is_readonly ) $js['readonly'][] = $field['fieldname'];
                    foreach ($datatypes as $datatype) {
                        if ($datatype == "M") {
                            $js['mandatory'][] = array($field['fieldname'], $field['fieldlabel']);
                            $is_mandatory = true;
                        }
                        if ($datatype == "I" && !$is_readonly){
                          if($field['uitype_id'] == 35){
                            $js['integer'][] = array($field['fieldname'].'_from', $field['fieldlabel']);
                            $js['integer'][] = array($field['fieldname'].'_to', $field['fieldlabel']);
                          }
                          else
                            $js['integer'][] = array($field['fieldname'], $field['fieldlabel']);
                        }
                        if ($datatype == "F" && !$is_readonly){
                              if($field['uitype_id'] == 35) {
                                $js['float'][] = array($field['fieldname'].'_from', $field['fieldlabel']);
                                $js['float'][] = array($field['fieldname'].'_to', $field['fieldlabel']);
                              }
                              else {
                                $js['float'][] = array($field['fieldname'], $field['fieldlabel']);
                              }
                        }
                        
                        if ($datatype == "N")
                            $js['numeric'][] = array($field['fieldname'], $field['fieldlabel']);

                        if ($datatype == "E")
                            $js['email'][] = array($field['fieldname'], $field['fieldlabel']);
                        if ($datatype == "U")
                            $js['url'][] = array($field['fieldname'], $field['fieldlabel']);
                        if ($datatype == "P")
                            $js['password'][] = array($field['fieldname'], $field['fieldlabel']);
                        if (preg_match("/LE/", $datatype) > 0)
                            $js['le'][] = array($field['fieldname'], $field['fieldlabel'], substr($datatype, 2));
                        if (preg_match("/LT/", $datatype) > 0)
                            $js['lt'][] = array($field['fieldname'], $field['fieldlabel'], substr($datatype, 2));
                        if (preg_match("/GE/", $datatype) > 0)
                            $js['ge'][] = array($field['fieldname'], $field['fieldlabel'], substr($datatype, 2));
                        if (preg_match("/GT/", $datatype) > 0)
                            $js['gt'][] = array($field['fieldname'], $field['fieldlabel'], substr($datatype, 2));
                    }

                    $this->uitype_edit->showFieldInputBlank($field, $is_mandatory, $fieldgroup['use_tabindex']);
                    if ($field['uitype_id'] == 5 && !$is_readonly)
                        $js['date'][] = array($field['fieldname'], $field['fieldlabel']);
                    if ($field['uitype_id'] == 24 && !$is_readonly)
                        $js['date_from_to'][] = array($field['fieldname'], $field['fieldlabel']);
                    if ($field['uitype_id'] == 26 && !$is_readonly)
                        $js['time_start_end'][] = array($field['fieldname'], $field['fieldlabel']);
                    if ($field['uitype_id'] == 13)
                        $load_jqgrid_in_boxy = true;
                    if ($field['uitype_id'] == 16) {
                        $js['ckeditor'][] = array($field['fieldname'], $field['fieldlabel']);
                        $load_ckeditor = true;
                    }
                    if ($field['uitype_id'] == 11) {
                        $js['single_upload'][] = array($field['fieldname'], $field['fieldlabel']);
                        $load_uploadify = true;
                    }
                    if ($field['uitype_id'] == 20) {
                        $js['multiple_upload'][] = array($field['fieldname'], $field['fieldlabel'], $field['field_id']);
                        $load_uploadify = true;
                    }
                    if ($field['uitype_id'] == 21) {
                        $js['multiselect'][] = array($field['fieldname'], $field['fieldlabel']);
                        $load_multiselect = true;
                    }
                    if ($field['uitype_id'] == 39 || $field['uitype_id'] == 28) {
                        $js['chosen_autocomplete'][] = array($field['fieldname'], $field['fieldlabel']);
                        $chosen_autocomplete = true;
                    }                                        
                endforeach;
                //create js validation
                if ($show_wizard_control) {
                    $js['fg_id'] = $fieldgroup['fieldgroup_id'];
                    $this->load->view($this->userinfo['rtheme'] . '/template/edit-wizard-form-js', $js, FALSE, FALSE);
                    $js = array();
                }
            endif;
        ?>
        <br clear="all">
        <hr>
        <br />
    </div>
</div>
<div id="multiple-form-container">
    <?php
        $result = $this->db->get_where('module',array("module_id"=>$fieldgroup['module_id'],'deleted'=>0));
        if ($result && $result->num_rows() > 0):
            $row = $result->row();

            $this->db->where($row->key_field,$this->input->post('record_id'));
            $this->db->where('deleted',0);
            $result_info = $this->db->get($fieldgroup['fields'][0]['table']);

            if ($result_info && $result_info->num_rows() > 0):
    ?>
                <div class="form-multiple-add" style="display: block;">
                    <h3 class="form-head">
                        <div class="align-right">
                            <span class="fh-delete">
                                <a href="javascript:void(0)" class="delete-detail">DELETE</a>
                            </span>
                        </div>
                    </h3>
                    <?php
                        if (isset($fieldgroup['fields']) && sizeof($fieldgroup['fields']) > 0 && $fieldgroup['standard_custom_view'] == 1) :
                            foreach ($fieldgroup['fields'] as $field) :
                                //rename fieldname in to array
                                $field['fieldname'] = strtolower($fieldgroup['fieldgroup_label']).'['.$field['fieldname'].']'.'[]';
                                //set js validation params
                                $datatypes = explode('~', $field['datatype']);
                                $is_mandatory = false;
                                $is_readonly = in_array( 'R', $datatypes )? true : false;
                                if( $is_readonly ) $js['readonly'][] = $field['fieldname'];
                                foreach ($datatypes as $datatype) {
                                    if ($datatype == "M") {
                                        $js['mandatory'][] = array($field['fieldname'], $field['fieldlabel']);
                                        $is_mandatory = true;
                                    }
                                    if ($datatype == "I" && !$is_readonly){
                                      if($field['uitype_id'] == 35){
                                        $js['integer'][] = array($field['fieldname'].'_from', $field['fieldlabel']);
                                        $js['integer'][] = array($field['fieldname'].'_to', $field['fieldlabel']);
                                      }
                                      else
                                        $js['integer'][] = array($field['fieldname'], $field['fieldlabel']);
                                    }
                                    if ($datatype == "F" && !$is_readonly){
                                          if($field['uitype_id'] == 35) {
                                            $js['float'][] = array($field['fieldname'].'_from', $field['fieldlabel']);
                                            $js['float'][] = array($field['fieldname'].'_to', $field['fieldlabel']);
                                          }
                                          else {
                                            $js['float'][] = array($field['fieldname'], $field['fieldlabel']);
                                          }
                                    }
                                    
                                    if ($datatype == "N")
                                        $js['numeric'][] = array($field['fieldname'], $field['fieldlabel']);

                                    if ($datatype == "E")
                                        $js['email'][] = array($field['fieldname'], $field['fieldlabel']);
                                    if ($datatype == "U")
                                        $js['url'][] = array($field['fieldname'], $field['fieldlabel']);
                                    if ($datatype == "P")
                                        $js['password'][] = array($field['fieldname'], $field['fieldlabel']);
                                    if (preg_match("/LE/", $datatype) > 0)
                                        $js['le'][] = array($field['fieldname'], $field['fieldlabel'], substr($datatype, 2));
                                    if (preg_match("/LT/", $datatype) > 0)
                                        $js['lt'][] = array($field['fieldname'], $field['fieldlabel'], substr($datatype, 2));
                                    if (preg_match("/GE/", $datatype) > 0)
                                        $js['ge'][] = array($field['fieldname'], $field['fieldlabel'], substr($datatype, 2));
                                    if (preg_match("/GT/", $datatype) > 0)
                                        $js['gt'][] = array($field['fieldname'], $field['fieldlabel'], substr($datatype, 2));
                                }

                                $this->uitype_edit->showFieldInput($field, $is_mandatory, $fieldgroup['use_tabindex']);
                                if ($field['uitype_id'] == 5 && !$is_readonly)
                                    $js['date'][] = array($field['fieldname'], $field['fieldlabel']);
                                if ($field['uitype_id'] == 24 && !$is_readonly)
                                    $js['date_from_to'][] = array($field['fieldname'], $field['fieldlabel']);
                                if ($field['uitype_id'] == 26 && !$is_readonly)
                                    $js['time_start_end'][] = array($field['fieldname'], $field['fieldlabel']);
                                if ($field['uitype_id'] == 13)
                                    $load_jqgrid_in_boxy = true;
                                if ($field['uitype_id'] == 16) {
                                    $js['ckeditor'][] = array($field['fieldname'], $field['fieldlabel']);
                                    $load_ckeditor = true;
                                }
                                if ($field['uitype_id'] == 11) {
                                    $js['single_upload'][] = array($field['fieldname'], $field['fieldlabel']);
                                    $load_uploadify = true;
                                }
                                if ($field['uitype_id'] == 20) {
                                    $js['multiple_upload'][] = array($field['fieldname'], $field['fieldlabel'], $field['field_id']);
                                    $load_uploadify = true;
                                }
                                if ($field['uitype_id'] == 21) {
                                    $js['multiselect'][] = array($field['fieldname'], $field['fieldlabel']);
                                    $load_multiselect = true;
                                }
                                if ($field['uitype_id'] == 39 || $field['uitype_id'] == 28) {
                                    $js['chosen_autocomplete'][] = array($field['fieldname'], $field['fieldlabel']);
                                    $chosen_autocomplete = true;
                                }                                        
                            endforeach;
                            //create js validation
                            if ($show_wizard_control) {
                                $js['fg_id'] = $fieldgroup['fieldgroup_id'];
                                $this->load->view($this->userinfo['rtheme'] . '/template/edit-wizard-form-js', $js, FALSE, FALSE);
                                $js = array();
                            }
                        endif;
                    ?>
                    <br clear="all">
                    <hr>
                    <br />
                </div>    
    <?php 
            endif; 
        endif;
    ?>
    <input class="add-more-flag" type="hidden" value="general">
</div>

<script type="text/javascript">
    $(document).ready(function () {
        var container = $('#form-multiple-add-container').html();
        $('#form-multiple-add-container').find('*').attr('name', 'tmp');
        $(".scv .icon-label-group,.form-submit-btn .icon-label-group").each(function( index, domEle ){
            $(this).append('<div style="" class="icon-label add-more-div"><a href="javascript:void(0);" class="icon-16-add icon-16-add-listview add-more" rel="skill"><span>Add</span></a></div>');            
        });

        $('.add-more').live('click',function(){
            $('#multiple-form-container').append(container);
        });
        $('.delete-detail').live('click',function(){
            $(this).closest('.form-multiple-add').remove();
        });
    });
</script>