<?php
$ci =& get_instance();

$parent_id = get_parent($ci->module_id);

$ci->db->where('module_id', $parent_id);
$parent = $ci->db->get('module')->row_array();

$side_nav = $header_nav[$parent_id];
?>
<div class="sidebar-wrap">

    <script>
        $(function() {
            var availableTags = [
                "ActionScript",
                "AppleScript",
                "Asp",
                "BASIC",
                "C",
                "C++",
                "Clojure",
                "COBOL",
                "ColdFusion",
                "Erlang",
                "Fortran",
                "Groovy",
                "Haskell",
                "Java",
                "JavaScript",
                "Lisp",
                "Perl",
                "PHP",
                "Python",
                "Ruby",
                "Scala",
                "Scheme"
            ];
            $( "#searchbar" ).autocomplete({
                source: availableTags
            });
        });
	

	
    </script>



    <div class="related-links">
        <h3><?php echo $parent['short_name']; ?></h3>
        <?php create_side_nav($side_nav['child']); ?>        
    </div>

    <div class="searchbar ui-widget">
        <input type="text" id="searchbar" onblur="if (this.value == '') {this.value = 'Search Site';}" onfocus="if (this.value == 'Search Site') {this.value = '';}" class="input-text" value="Search Site">

    </div>
    <!--<div class="demo">
    
    <div class="ui-widget">
            <label for="tags">Tags: </label>
            <input id="tags" />
    </div>
    
    </div>
    -->

</div>	




