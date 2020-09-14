$(document).ready(function(){
      $('#employee_id').multiselect().multiselectfilter({show:['blind',250],hide:['blind',250],selectedList: 1});
      $("#jqgridcontainer").jqGrid({
            url: module.get_value('base_url') + module.get_value('module_link') + '/listview',
            loadtext: '<img src="'+ module.get_value('base_url') + user.get_value('user_theme') + '/images/loading.gif"><br />Loading...',
            datatype: "json",
            mtype: "POST",
            rowNum: 25,            
            rowList: [10, 15, 25, 40, 60, 85, 100],
            toolbar: [true,"top"],
            height: 'auto',
            autowidth: true,
            pager: "#jqgridpager",
            pagerpos: 'right',
            toppager: true,
            viewrecords: true,
            altRows: true,
            forceFit: true,
            shrinkToFit: true,
            colNames:["id", "Employee", "Employee No", "Payroll Date", "Date Uploaded", "Action"],
             colModel:[
                  {name: 'id', hidden: true},
                  {name: 'employee', align: 'left', sortable:false, width:'200px'},
                  {name: 'employee_no', align: 'left', sortable:false, width:'150px'},
                  {name: 'payroll_date', align: 'center', sortable:false, width:'150px'},
                  {name: 'upload_date', align: 'center', sortable:false, width:'150px'},
                  {name: 'action', align: 'center', sortable:false, width:'100px'},
             ]
      });

      $("#jqgridcontainer").jqGrid('navGrid','#jqgridpager',{refresh:false, edit:false, add:false, del:false, search:false});
      $("#jqgridcontainer_toppager_center").hide();
      $("#jqgridpager_center").hide();

      $(".search-trigger[tooltip]").tipsy({
            title: 'tooltip',
            gravity: 'se',
            opacity: 0.85,
            live: true,
            delayIn: 500
      });
});

function get_data(){
      $("#jqgridcontainer").jqGrid('clearGridData', {   clearfooter: true});
      var employee_id = $('#employee_id').val();

      $("#jqgridcontainer").jqGrid('clearGridData', { clearfooter: true});
      $("#jqgridcontainer").GridUnload();
      $("#jqgridcontainer").jqGrid({
            url: module.get_value('base_url') + module.get_value('module_link') + '/listview',
            loadtext: '<img src="'+ module.get_value('base_url') + user.get_value('user_theme') + '/images/loading.gif"><br />Loading...',
            datatype: "json",
            mtype: "POST",
            rowNum: 25,            
            rowList: [10, 15, 25, 40, 60, 85, 100],
            toolbar: [true,"top"],
            height: 'auto',
            autowidth: true,
            pager: "#jqgridpager",
            pagerpos: 'right',
            toppager: true,
            viewrecords: true,
            altRows: true,
            forceFit: true,
            shrinkToFit: true,
            colNames:["id", "Employee", "Employee No", "Payroll Date", "Date Uploaded", "Action"],
            colModel:[
                  {name: 'id', hidden: true},
                  {name: 'employee', align: 'left', sortable:false, width:'200px'},
                  {name: 'employee_no', align: 'left', sortable:false, width:'150px'},
                  {name: 'payroll_date', align: 'center', sortable:false, width:'150px'},
                  {name: 'upload_date', align: 'center', sortable:false, width:'150px'},
                  {name: 'action', align: 'center', sortable:false, width:'100px'},
            ],
            postData: {
                  employee_id: employee_id,
            },
      });
}