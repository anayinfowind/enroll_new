<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Admin settings and defaults
 *
 * @package enrol_leeloolxp_enroll
 * @copyright  2020 Leeloo LXP (https://leeloolxp.com)
 * @author Leeloo LXP <info@leeloolxp.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();



    class enrol_leeloolxp_enroll_observer

    {

        
       
        public static function viewed_activity(\core\event\course_module_viewed $events) {
             /*
                
             */
            global $USER;
            global $PAGE;

            $config_leeloolxp_web_login_tracking = get_config('enrol_leeloolxp_enroll');
            $liacnse_key =  $config_leeloolxp_web_login_tracking->leeloolxp_licensekey;

            $postData = '&license_key='.$liacnse_key;



            $ch = curl_init();  

            $url = 'https://leeloolxp.com/api_moodle.php/?action=page_info';

            curl_setopt($ch,CURLOPT_URL,$url);

            curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);

            curl_setopt($ch,CURLOPT_HEADER, false); 

            curl_setopt($ch, CURLOPT_POST, count($postData));

            curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);  

            $output=curl_exec($ch);

            curl_close($ch);



            $info_teamnio  = json_decode($output);

            $teamnio_url = $info_teamnio->data->install_url;
            $event_data = $events->get_data();
            $userid = $event_data['userid'];
            $courseid = $event_data['courseid'];
            $contextinstanceid = $event_data['contextinstanceid'];
            $component = $event_data['component'];
            $postdata = '&moodle_user_id='.$userid.'&course_id='.$courseid.'&activity_id='.$contextinstanceid."&mod_name=".$component."&user_email=".$USER->email;
            

             $ch = curl_init();  

                $url = $teamnio_url.'/admin/sync_moodle_course/update_viewed_log';

                $url = str_replace("'", '', $url);

                curl_setopt($ch,CURLOPT_URL,$url);

                curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);

                curl_setopt($ch,CURLOPT_HEADER, false); 

                curl_setopt($ch,CURLOPT_POST, count($postdata));

                curl_setopt($ch,CURLOPT_POSTFIELDS, $postdata);  

                $outputs = curl_exec($ch);



               

           
            
        }

        public static function completion_updated(\core\event\course_module_completion_updated $event) {

            global $DB;

            $moduleid = $event->contextinstanceid;

            $userid = $event->userid;

            $courseid = $event->courseid;

            $completionstate = $event->other['completionstate'];

            

            $user = $DB->get_record('user', array('id' => $userid));



            $email = $user->email;



            $config_leeloolxp_web_login_tracking = get_config('enrol_leeloolxp_enroll');

            $liacnse_key =  $config_leeloolxp_web_login_tracking->leeloolxp_licensekey;

       	    $postData = '&license_key='.$liacnse_key;



            $ch = curl_init();      

            $url = 'https://leeloolxp.com/api_moodle.php/?action=page_info';

            curl_setopt($ch,CURLOPT_URL,$url);

            curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);

            curl_setopt($ch,CURLOPT_HEADER, false); 

            curl_setopt($ch, CURLOPT_POST, count($postData));

            curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);  

            $output=curl_exec($ch);

            curl_close($ch);



            $info_teamnio  = json_decode($output);



            if($info_teamnio->status!='false') {

                

                $teamnio_url = $info_teamnio->data->install_url;

                

                $postData = '&email='.$user->email.'&completionstate='.$completionstate.'&activity_id='.$moduleid;



                $ch = curl_init();  

                $url = $teamnio_url.'/admin/sync_moodle_course/mark_completed_by_moodle_user';

                curl_setopt($ch,CURLOPT_URL,$url);

                curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);

                curl_setopt($ch,CURLOPT_HEADER, false); 

                curl_setopt($ch, CURLOPT_POST, count($postData));

                curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);  

                $output=curl_exec($ch);

                curl_close($ch);

                

            } else {

            	return true;

           	}	

            

            return true;

        }

        public static function edit_user(\core\event\user_updated $event) {

            $data = $event->get_data();

            global $DB;


            $user = core_user::get_user($data['relateduserid'], '*', MUST_EXIST);

            $name =  ucfirst(str_replace("'", '',$user->firstname))."".ucfirst(str_replace("'",'', $user->lastname));

            $email = str_replace("'", '', $user->email);

            $config_leeloolxp_web_login_tracking = get_config('enrol_leeloolxp_enroll');

            $liacnse_key =  $config_leeloolxp_web_login_tracking->leeloolxp_licensekey;

            $postData = '&license_key='.$liacnse_key;



            $ch = curl_init();  

            $url = 'https://leeloolxp.com/api_moodle.php/?action=page_info';

            curl_setopt($ch,CURLOPT_URL,$url);

            curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);

            curl_setopt($ch,CURLOPT_HEADER, false); 

            curl_setopt($ch, CURLOPT_POST, count($postData));

            curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);  

            $output=curl_exec($ch);

            curl_close($ch);



            $info_teamnio  = json_decode($output);


            $countries = get_string_manager()->get_list_of_countries();
            if($info_teamnio->status!='false') {
                   
                

                $teamnio_url = $info_teamnio->data->install_url;

                $user_interests =  $DB->get_records_sql("SELECT * FROM {tag} as t JOIN {tag_instance} as ti  ON ti.tagid = t.id JOIN {user} as u ON u.id = ti.itemid AND ti.itemtype = 'user' AND u.username = '$user->username'");
                $user_interests_arr = array();
                if(!empty($user_interests)){
                    foreach ($user_interests as $key => $value) {
                        $user_interests_arr[]  = $value->name;
                    }
                }
                
                $user_interests_string = implode(',',$user_interests_arr);

                $lastlogin = date('Y-m-d h:i:s',$user->lastlogin);
                $fullname = ucfirst($user->firstname)." ".ucfirst($user->middlename)." ".ucfirst($user->lastname);
                $city =  $user->city;
                $country = $countries[$user->country];
                $timezone = $user->timezone;
                $skype = $user->skype;
                $idnumber = $user->idnumber;
                $institution = $user->institution;
                $department = $user->department;
                $phone = $user->phone1;
                $moodle_phone = $user->phone2;
                $address = $user->address;
                $firstaccess = $user->firstaccess;
                $lastaccess = $user->lastaccess;
                $lastlogin = $lastlogin;
                $lastip = $user->lastip;
                $interests = $user_interests_string;
                $description = $user->description;
                $description_of_pic = $user->imagealt;
                $alternatename = $user->alternatename;
                $web_page = $user->url;

                $sql = "SELECT ud.data  FROM {user_info_data} ud JOIN {user_info_field} uf ON uf.id = ud.fieldid WHERE ud.userid = :userid AND uf.shortname = :fieldname";
                $params = array('userid' =>  $user->id, 'fieldname' => 'degree');

                $degree = $DB->get_field_sql($sql, $params);
                $sql = "SELECT ud.data FROM {user_info_data} ud JOIN {user_info_field} uf ON uf.id = ud.fieldid WHERE ud.userid = :userid AND uf.shortname = :fieldname"; $params = array('userid' =>  $user->id, 'fieldname' => 'Pathway');
                $pathway = $DB->get_field_sql($sql, $params);




                $img_url =  new moodle_url('/user/pix.php/'.$user->id.'/f1.jpg');
                
                
                $postData = '&email='.$email.'&name='.$fullname.'&city='.$city.'&country='.$country.'&timezone='.$timezone.'&skype='.$skype.'&idnumber='.$idnumber.'&institution='.$institution.'&department='.$department.'&phone='.$phone.'&moodle_phone='.$moodle_phone.'&address='.$address.'&firstaccess='.$firstaccess.'&lastaccess='.$lastaccess.'&lastlogin='.$lastlogin.'&lastip='.$lastip.'&description='.$description.'&description_of_pic='.$description_of_pic.'&alternatename='.$alternatename.'&web_page='.$web_page.'&img_url='.$img_url.'&interests='.$interests.'&degree='.$degree.'&pathway='.$pathway;

                   /* $fp = fopen('/home/tblue/moodledemo.tblue.io/files.txt', 'w');
                    fwrite($fp, print_r($Pathway, TRUE));
                    fclose($fp);
                    $h = fopen('/home/tblue/moodledemo.tblue.io/files.txt', 'r+');
                    fwrite($h, var_export($Pathway, true));*/
                 

                $ch = curl_init();  

                $url = $teamnio_url.'/admin/sync_moodle_course/update_username';

                $url = str_replace("'", '', $url);

                curl_setopt($ch,CURLOPT_URL,$url);

                curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);

                curl_setopt($ch,CURLOPT_HEADER, false); 

                curl_setopt($ch,CURLOPT_POST, count($postData));

                curl_setopt($ch,CURLOPT_POSTFIELDS, $postData);  

                $outputs = curl_exec($ch);



                curl_close($ch);

                

                

            }

        }

        public static function group_member_added(\core\event\group_member_added $events) {

            

            global $DB;

            $group = $events->get_record_snapshot('groups', $events->objectid);

            $user = core_user::get_user($events->relateduserid, '*', MUST_EXIST);

            $email = str_replace("'", '', $user->email);

            $courseid = str_replace("'", '', $courseid);

            $group_name = str_replace("'", '', $group->name);

            



            $config_leeloolxp_web_login_tracking = get_config('enrol_leeloolxp_enroll');

            $liacnse_key =  $config_leeloolxp_web_login_tracking->leeloolxp_licensekey;

            $postData = '&license_key='.$liacnse_key;



            $ch = curl_init();  

            $url = 'https://leeloolxp.com/api_moodle.php/?action=page_info';

            curl_setopt($ch,CURLOPT_URL,$url);

            curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);

            curl_setopt($ch,CURLOPT_HEADER, false); 

            curl_setopt($ch, CURLOPT_POST, count($postData));

            curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);  

            $output=curl_exec($ch);

            curl_close($ch);



            $info_teamnio  = json_decode($output);



            if($info_teamnio->status!='false') {

                

                $teamnio_url = $info_teamnio->data->install_url;

                

                $postData = '&email='.$user->email.'&courseid='.$courseid.'&group_name='.$group_name;



                $ch = curl_init();  

               

                

            } else {

                return true;

            }

           

        }

        public static function role_assign(\core\event\role_assigned $enrolment_data) {

            global $DB;

            $enrolment_data_data = $enrolment_data->get_data();

            $snapshotid = $enrolment_data->get_data()['other']['id'];        

            $snapshot = $enrolment_data->get_record_snapshot('role_assignments', $snapshotid);



            $roleid = $snapshot->roleid;



            $user_type = '';

            $teamnio_role = '';

            $teamnio_user_type = '';

            

            $user = $DB->get_record('user', array('id' => $enrolment_data->relateduserid));

            $course = $DB->get_record('course', array('id' => $enrolment_data->courseid));

            // get user custom field degree

            $user_degree = $DB->get_record_sql("SELECT DISTINCT data  FROM {user_info_data} as fdata left join {user_info_field} as fieldss on fdata.fieldid = fieldss.id where fieldss.shortname = 'degree' and fdata.userid = '$user->id'");

            $user_degree_name = $user_degree->data;

            $user_department = $user->department;

            $user_institution = $user->institution;

            $sso_plugin_config =  get_config('leeloolxp_tracking_sso');
    
            $student_num_combinations_val = $sso_plugin_config->student_num_combination;
            
            $student_db_set_arr = array();

            for($si=1; $student_num_combinations_val>=$si;$si++) {

                $student_position_moodle = 'student_position_moodle_'.$si; 

                $m_student_role = $sso_plugin_config->$student_position_moodle;

                $student_institution = 'student_institution_'.$si; 

                $m_student_institution = $sso_plugin_config->$student_institution;

                $student_department = 'student_department_'.$si; 

                $m_student_department = $sso_plugin_config->$student_department;

                $student_degree = 'student_degree_'.$si; 

                $m_student_degree = $sso_plugin_config->$student_degree;

                $student_db_set_arr[$si] = $m_student_role."_".$m_student_institution."_".$m_student_department."_".$m_student_degree;



            }

               



            $user_student_info =  $roleid."_".$user_institution."_".$user_department."_".$user_degree_name;

            $matched_value = array_search($user_student_info, $student_db_set_arr);

            

            

            if ($matched_value) { 

                $t_col_name_student = 'student_position_t_'.$matched_value;

                $teamnio_student_role =  $sso_plugin_config->$t_col_name_student;

                if(!empty($teamnio_student_role)) {

                   $teamnio_role =  $teamnio_student_role;

                }

                $user_type = 'student';

            } else {
                $teacher_num_combinations_val = $sso_plugin_config->teacher_num_combination;

                $teacher_db_set_arr = array();

                for($si=1; $teacher_num_combinations_val>=$si;$si++) {

                    $teacher_position_moodle = 'teacher_position_moodle_'.$si; 

                    $m_teacher_role = $sso_plugin_config->$teacher_position_moodle;



                    $teacher_institution = 'teacher_institution_'.$si; 


                    $m_teacher_institution = $sso_plugin_config->$teacher_institution;

                    $teacher_department = 'teacher_department_'.$si; 

                    $m_teacher_department = $sso_plugin_config->$teacher_department;



                    $teacher_degree = 'teacher_degree_'.$si; 


                    $m_teacher_degree = $sso_plugin_config->$teacher_degree;;



                    $teacher_db_set_arr[$si] = $m_teacher_role."_".$m_teacher_institution."_".$m_teacher_department."_".$m_teacher_degree;



                }

                


                $user_teacher_info =  $roleid."_".$user_institution."_".$user_department."_".$user_degree_name;

                $matched_value_teacher = array_search($user_teacher_info, $teacher_db_set_arr);

                

                

                if ($matched_value_teacher) { 

                    $t_col_name_teacher = 'teacher_position_t_'.$matched_value_teacher;

                    $teamnio_teacher_role = $sso_plugin_config->$t_col_name_teacher;

                    if(!empty($teamnio_teacher_role)) {

                       $teamnio_role =  $teamnio_teacher_role;

                    }

                        $user_type = 'teacher';

                    

                } else {

                    // both student and rteacher not matched then get default role.

                    $user_type = 'student';


                    $teamnio_role = $sso_plugin_config->default_student_position;

                }

            }

            

            

                

            if($user_type=='student') {

                $can_create_user = $sso_plugin_config->web_new_user_student;

                $user_approval = $sso_plugin_config->required_aproval_student;

                $user_designation = $teamnio_role;

            } else { 

                if($user_type=='teacher') {

                    $can_create_user = $sso_plugin_config->web_new_user_teacher;

                    $user_designation = $teamnio_role;

                    $user_approval = $sso_plugin_config->required_aproval_teacher; 

                } 

            }



            $config_leeloolxp_web_login_tracking = get_config('enrol_leeloolxp_enroll');

            $liacnse_key =  $config_leeloolxp_web_login_tracking->leeloolxp_licensekey;

            $postData = '&license_key='.$liacnse_key;



            $ch = curl_init();  



            $url = 'https://leeloolxp.com/api_moodle.php/?action=page_info';



            curl_setopt($ch,CURLOPT_URL,$url);



            curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);



            curl_setopt($ch,CURLOPT_HEADER, false); 



            curl_setopt($ch, CURLOPT_POST, count($postData));



            curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);  



            $output=curl_exec($ch);



            curl_close($ch);



            $info_teamnio  = json_decode($output);

            if($info_teamnio->status!='false') {

                $teamnio_url = $info_teamnio->data->install_url;

            } else {

                $teamnio_url ='';

                return false;

            }   



           

            $lastlogin = date('Y-m-d h:i:s',$user->lastlogin);

            $fullname = ucfirst($user->firstname)." ".ucfirst($user->middlename)." ".ucfirst($user->lastname);

            $city =  $user->city;

            $country = $user->country;

            $timezone = $user->timezone;

            $skype = $user->skype;

            $idnumber = $user->idnumber;

            $institution = $user->institution;

            $department = $user->department;

            $phone = $user->phone1;

            $moodle_phone = $user->phone2;

            $adress = $user->adress;

            $firstaccess = $user->firstaccess;

            $lastaccess = $user->lastaccess;

            $lastlogin = $lastlogin;

            $lastip = $user->lastip;

            

            

            $description = $user->description;

            $description_of_pic = $user->imagealt;

            $alternatename = $user->alternatename;

            $web_page = $user->url;



            $moodle_url_pic = new moodle_url('/user/pix.php/'.$user->id.'/f1.jpg');

            $moodle_pic_data =  file_get_contents($moodle_url_pic);

            

            $postData = '&email='.$user->email.'&username='.$user->username.'&fullname='.$fullname."&courseid=".$enrolment_data->courseid.'&designation='.$user_designation."&user_role=".$teamnio_role."&user_approval=".$user_approval."&can_user_create=".$can_create_user."&user_type=".$user_type."&city=".$city."&country=".$country."&timezone=".$timezone."&skype=".$skype."&idnumber=".$idnumber."&institution=".$institution."&department=".$department."&phone=".$phone."&moodle_phone=".$moodle_phone."&adress=".$adress."&firstaccess=".$firstaccess."&lastaccess=".$lastaccess."&lastlogin=".$lastlogin."&lastip=".$lastip."&user_profile_pic=".urlencode($moodle_pic_data)."&user_description=".$description."&picture_description=".$description_of_pic."&institution=".$institution."&alternate_name=".$alternatename."&web_page=".$web_page;

            

   

            $ch = curl_init();  



            $url = $teamnio_url.'/admin/sync_moodle_course/enrolment_newuser';



            curl_setopt($ch,CURLOPT_URL,$url);



            curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);



            curl_setopt($ch,CURLOPT_HEADER, false); 



            curl_setopt($ch, CURLOPT_POST, count($postData));



            curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);  



            $output=curl_exec($ch);
        }


}