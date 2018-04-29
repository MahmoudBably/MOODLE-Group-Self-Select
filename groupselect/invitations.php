<?php
require('../../config.php');
require_once('locallib.php');

$verify1 = $DB->count_records('role_assignments', array('userid'=>$USER->id,'roleid'=>3));
$verify2 = $DB->count_records('role_assignments', array('userid'=>$USER->id,'roleid'=>4));
if($verify1>=1 || $verify2>=1){
$PAGE->set_context(get_system_context());
$PAGE->set_title("Supervision Invitations");
$PAGE->set_heading("Supervision Invitations");

$PAGE->set_url( '/mod/groupselect/invitations.php');
$my_invitations = get_my_invitations($USER->id);
$accept = optional_param( 'accept', 0, PARAM_INT );
$reject = optional_param( 'reject', 0, PARAM_INT );
$group_id = optional_param( 'group_id', 0, PARAM_INT );
$instance_id = optional_param( 'instance_id', 0, PARAM_INT );
$strgroup = get_string( 'group' );
$strgroupdesc = get_string( 'groupdescription', 'group' );
$straction = get_string( 'action', 'mod_groupselect' );

echo $OUTPUT->header();
echo '<button onclick="history.go(-1);">Back </button>';

if ($accept and isset( $my_invitations[$accept] )) {
    
   $check1 = $DB->count_records('groupselect_groups_teachers', array('groupid'=>$group_id));
   if($check1 == 1){
     echo '<script type="text/javascript">alert("Sorry! A Supervisor Has Been Already Assigned To This Group.");</script>';
     $DB->delete_records( 'groupselect_invitations', array (
                    'id' => $accept
            ) );
     redirect ( $PAGE->url );
   }else{
       $newgroupsupervisor = ( object ) array (
                    'groupid' => $group_id,
                    'teacherid' => $USER->id,
                    'instance_id' => $instance_id,
            );
        $DB->insert_record( 'groupselect_groups_teachers', $newgroupsupervisor );
        $DB->delete_records( 'groupselect_invitations', array (
                    'id' => $accept
            ) );
        redirect ( $PAGE->url );
   }
        
} 

if ($reject and isset( $my_invitations[$reject] )) {
    
        $DB->delete_records( 'groupselect_invitations', array (
                    'id' => $reject
            ) );
        redirect ( $PAGE->url );
   
        
} 


foreach ($my_invitations as $invitation) {
        
        $line = array ();
        
        // Student Name
        $line[0] = get_student_name($invitation->from_id);
        
        //Group Name
        $line[1] = $invitation->group_name;
        
        //Group Description
        $line[2] = $invitation->group_description;
                
        //Action Buttons
        $line[3] = $OUTPUT->single_button( new moodle_url( '/mod/groupselect/invitations.php', array (
                        'accept' => $invitation->id,
                        'group_id'=> $invitation->group_id,
                        'instance_id'=>$invitation->instance_id
                ) ), get_string( 'accept', 'mod_groupselect', ""));
        $actionpresent = true;                

        $line[4] = $OUTPUT->single_button( new moodle_url( '/mod/groupselect/invitations.php', array (
                        'reject' => $invitation->id,
                ) ), get_string( 'reject', 'mod_groupselect', ""));
        
       
                
                
        $data[] = $line;
    }   





 $sortscript = file_get_contents( './lib/sorttable/sorttable.js' );
    echo html_writer::script( $sortscript );
    $table = new html_table();
    $table->attributes = array (
            'class' => 'generaltable sortable invitations-table',
    );
    $table->head = array (
            'Student Name',
            $strgroup,
            $strgroupdesc,
            ''
    );
    if ($actionpresent) {
        array_push($table->head, $straction);
    }

    $table->data = $data;
    echo html_writer::table( $table );
    
    
echo $OUTPUT->footer();

}else{
   $url= '/';
   redirect($url);
}