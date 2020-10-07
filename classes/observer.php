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
/**
 * Plugin to sync users on new enroll, groups, trackign of activity view to LeelooLXP account of the Moodle Admin
 */
class enrol_leeloolxp_enroll_observer {
    /**
     * Plugin to sync users on new enroll, groups, trackign of activity view to LeelooLXP account of the Moodle Admin
     */
    public static function viewed_activity(\core\event\course_module_viewed $events) {
        global $USER;
        $configenroll = get_config('enrol_leeloolxp_enroll');
        $liacnsekey = $configenroll->leeloolxp_licensekey;
        $postdata = '&license_key=' . $liacnsekey;
        $url = 'https://leeloolxp.com/api_moodle.php/?action=page_info';
        $curl = new curl;
        $options = array(
            'CURLOPT_RETURNTRANSFER' => true,
            'CURLOPT_HEADER' => false,
            'CURLOPT_POST' => count($postdata),
        );
        if (!$output = $curl->post($url, $postdata, $options)) {
            return true;
        }
        $infoteamnio = json_decode($output);
        $teamniourl = $infoteamnio->data->install_url;
        $eventdata = $events->get_data();
        $userid = $eventdata['userid'];
        $courseid = $eventdata['courseid'];
        $contextinstanceid = $eventdata['contextinstanceid'];
        $component = $eventdata['component'];
        $postdata = '&moodle_user_id=' . $userid . '&course_id=' . $courseid . '&activity_id=' . $contextinstanceid . "&mod_name=" . $component . "&user_email=" . $USER->email;
        $url = $infoteamnio . '/admin/sync_moodle_course/update_viewed_log';
        $outputs = curl_exec($ch);
        $curl = new curl;
        $options = array(
            'CURLOPT_RETURNTRANSFER' => true,
            'CURLOPT_HEADER' => false,
            'CURLOPT_POST' => count($postdata),
        );
        if (!$output = $curl->post($url, $postdata, $options)) {
            return true;
        }
    }
    /**
     * Plugin to sync users on new enroll, groups, trackign of activity view to LeelooLXP account of the Moodle Admin
     */
    public static function completion_updated(\core\event\course_module_completion_updated $event) {
        global $DB;
        $moduleid = $event->contextinstanceid;
        $userid = $event->userid;
        $courseid = $event->courseid;
        $completionstate = $event->other['completionstate'];
        $user = $DB->get_record('user', array('id' => $userid));
        $email = $user->email;
        $configenroll = get_config('enrol_leeloolxp_enroll');
        $liacnsekey = $configenroll->leeloolxp_licensekey;
        $postdata = '&license_key=' . $liacnsekey;
        $url = 'https://leeloolxp.com/api_moodle.php/?action=page_info';
        $curl = new curl;
        $options = array(
            'CURLOPT_RETURNTRANSFER' => true,
            'CURLOPT_HEADER' => false,
            'CURLOPT_POST' => count($postdata),
        );
        if (!$output = $curl->post($url, $postdata, $options)) {
            return true;
        }
        $infoteamnio = json_decode($output);
        if ($infoteamnio->status != 'false') {
            $teamniourl = $infoteamnio->data->install_url;
            $postdata = '&email=' . $user->email . '&completionstate=' . $completionstate . '&activity_id=' . $moduleid;
            $url = $teamniourl . '/admin/sync_moodle_course/mark_completed_by_moodle_user';
            $curl = new curl;
            $options = array(
                'CURLOPT_RETURNTRANSFER' => true,
                'CURLOPT_HEADER' => false,
                'CURLOPT_POST' => count($postdata),
            );
            if (!$output = $curl->post($url, $postdata, $options)) {
                return true;
            }
        } else {
            return true;
        }
        return true;
    }
    /**
     * Plugin to sync users on new enroll, groups, trackign of activity view to LeelooLXP account of the Moodle Admin
     */
    public static function edit_user(\core\event\user_updated $event) {
        $data = $event->get_data();
        global $DB;
        $user = core_user::get_user($data['relateduserid'], '*', MUST_EXIST);
        $name = ucfirst(str_replace("'", '', $user->firstname))
        . "" . ucfirst(str_replace("'", '', $user->lastname));
        $email = str_replace("'", '', $user->email);

        $configenroll = get_config('enrol_leeloolxp_enroll');
        $liacnsekey = $configenroll->leeloolxp_licensekey;
        $postdata = '&license_key=' . $liacnsekey;
        $url = 'https://leeloolxp.com/api_moodle.php/?action=page_info';
        $curl = new curl;
        $options = array(
            'CURLOPT_RETURNTRANSFER' => true,
            'CURLOPT_HEADER' => false,
            'CURLOPT_POST' => count($postdata),
        );
        if (!$output = $curl->post($url, $postdata, $options)) {
            return true;
        }
        $infoteamnio = json_decode($output);

        $countries = get_string_manager()->get_list_of_countries();
        if ($infoteamnio->status != 'false') {
            $teamniourl = $infoteamnio->data->install_url;
            $userinterests = $DB->get_records_sql("SELECT * FROM {tag}
            as t JOIN {tag_instance} as ti  ON ti.tagid = t.id JOIN {user}
            as u ON u.id = ti.itemid AND ti.itemtype = 'user'
            AND u.username = '$user->username'");
            $userinterestsarr = array();
            if (!empty($userinterests)) {
                foreach ($userinterests as $key => $value) {
                    $userinterests_arr[] = $value->name;
                }
            }

            $userinterestsstring = implode(',', $userinterestsarr);

            $lastlogin = date('Y-m-d h:i:s', $user->lastlogin);
            $fullname = ucfirst($user->firstname) . " " . ucfirst($user->middlename) . " " . ucfirst($user->lastname);
            $city = $user->city;
            $country = $countries[$user->country];
            $timezone = $user->timezone;
            $skype = $user->skype;
            $idnumber = $user->idnumber;
            $institution = $user->institution;
            $department = $user->department;
            $phone = $user->phone1;
            $moodlephone = $user->phone2;
            $address = $user->address;
            $firstaccess = $user->firstaccess;
            $lastaccess = $user->lastaccess;
            $lastlogin = $lastlogin;
            $lastip = $user->lastip;
            $interests = $user_interests_string;
            $description = $user->description;
            $descriptionofpic = $user->imagealt;
            $alternatename = $user->alternatename;
            $webpage = $user->url;

            $sql = "SELECT ud.data  FROM {user_info_data} ud JOIN
            {user_info_field} uf ON uf.id = ud.fieldid WHERE ud.userid = :userid
            AND uf.shortname = :fieldname";
            $params = array('userid' => $user->id, 'fieldname' => 'degree');
            $degree = $DB->get_field_sql($sql, $params);
            $sql = "SELECT ud.data FROM {user_info_data} ud JOIN {user_info_field}
            uf ON uf.id = ud.fieldid WHERE ud.userid = :userid AND
            uf.shortname = :fieldname";
            $params = array('userid' => $user->id, 'fieldname' => 'Pathway');
            $pathway = $DB->get_field_sql($sql, $params);
            $imgurl = new moodle_url('/user/pix.php/' . $user->id . '/f1.jpg');

            $postdata = '&email=' . $email . '&name=' . $fullname . '&city=' . $city . '&country=' . $country . '&timezone=' . $timezone . '&skype=' . $skype . '&idnumber=' . $idnumber . '&institution=' . $institution . '&department=' . $department . '&phone=' . $phone . '&moodle_phone=' . $moodlephone . '&address=' . $address . '&firstaccess=' . $firstaccess . '&lastaccess=' . $lastaccess . '&lastlogin=' . $lastlogin . '&lastip=' . $lastip . '&description=' . $description . '&description_of_pic=' . $descriptionofpic . '&alternatename=' . $alternatename . '&web_page=' . $webpage . '&img_url=' . $imgurl . '&interests=' . $interests . '&degree=' . $degree . '&pathway=' . $pathway;

            /* $fp = fopen('/home/tblue/moodledemo.tblue.io/files.txt', 'w');
            fwrite($fp, print_r($Pathway, TRUE));
            fclose($fp);
            $h = fopen('/home/tblue/moodledemo.tblue.io/files.txt', 'r+');
            fwrite($h, var_export($Pathway, true));*/
            $url = $teamniourl . '/admin/sync_moodle_course/update_username';
            $curl = new curl;
            $options = array(
                'CURLOPT_RETURNTRANSFER' => true,
                'CURLOPT_HEADER' => false,
                'CURLOPT_POST' => count($postdata),
            );
            if (!$output = $curl->post($url, $postdata, $options)) {
                return true;
            }
        }
    }

    /**
     * Plugin to sync users on new enroll, groups, trackign of activity view to LeelooLXP account of the Moodle Admin
     */
    public static function group_member_added(\core\event\group_member_added $events) {
        $group = $events->get_record_snapshot('groups', $events->objectid);
        $user = core_user::get_user($events->relateduserid, '*', MUST_EXIST);
        $courseid = str_replace("'", '', $courseid);
        $groupname = str_replace("'", '', $group->name);
        $configenroll = get_config('enrol_leeloolxp_enroll');
        $liacnsekey = $configenroll->leeloolxp_licensekey;
        $postdata = '&license_key=' . $liacnsekey;
        $url = 'https://leeloolxp.com/api_moodle.php/?action=page_info';
        $curl = new curl;
        $options = array(
            'CURLOPT_RETURNTRANSFER' => true,
            'CURLOPT_HEADER' => false,
            'CURLOPT_POST' => count($postdata),
        );
        if (!$output = $curl->post($url, $postdata, $options)) {
            return true;
        }
        $infoteamnio = json_decode($output);

        if ($infoteamnio->status != 'false') {
            $teamniourl = $infoteamnio->data->install_url;
            $postdata = '&email=' . $user->email . '&courseid=' . $courseid . '&group_name=' . $group_name;
        } else {
            return true;
        }
    }

    /**
     * Plugin to sync users on new enroll, groups, trackign of activity view to LeelooLXP account of the Moodle Admin
     */
    public static function role_assign(\core\event\role_assigned $enrolmentdata) {
        global $DB;
        $enrolmentdatadata = $enrolmentdata->get_data();
        $snapshotid = $enrolmentdata->get_data()['other']['id'];
        $snapshot = $enrolmentdata->get_record_snapshot('role_assignments', $snapshotid);
        $roleid = $snapshot->roleid;
        $usertype = '';
        $teamniorole = '';
        $teamniousertype = '';
        $user = $DB->get_record('user', array('id' => $enrolmentdata->relateduserid));
        $course = $DB->get_record('course', array('id' => $enrolmentdata->courseid));
        $userdegree = $DB->get_record_sql("SELECT DISTINCT data  FROM {user_info_data} as fdata
        left join {user_info_field} as fieldss on fdata.fieldid = fieldss.id where fieldss.shortname =
        'degree' and fdata.userid = '$user->id'");
        $userdegreename = $userdegree->data;
        $userdepartment = $user->department;
        $userinstitution = $user->institution;
        $ssopluginconfig = get_config('leeloolxp_tracking_sso');
        $studentnumcombinationsval = $ssopluginconfig->student_num_combination;
        $studentdbsetarr = array();

        for ($si = 1; $studentnumcombinationsval >= $si; $si++) {
            $studentpositionmoodle = 'student_position_moodle_' . $si;

            $mstudentrole = $ssopluginconfig->$studentpositionmoodle;

            $studentinstitution = 'student_institution_' . $si;

            $mstudentinstitution = $ssopluginconfig->$studentinstitution;

            $studentdepartment = 'student_department_' . $si;

            $mstudentdepartment = $ssopluginconfig->$studentdepartment;

            $studentdegree = 'student_degree_' . $si;

            $mstudentdegree = $ssopluginconfig->$studentdegree;

            $studentdbsetarr[$si] = $mstudentrole . "_" . $mstudentinstitution . "_" .
                $mstudentdepartment . "_" . $mstudentdegree;
        }
        $userstudentinfo = $roleid . "_" . $userinstitution . "_" . $userdepartment . "_" .
            $userdegreename;

        $matchedvalue = array_search($userstudentinfo, $studentdbsetarr);

        if ($matchedvalue) {
            $tcolnamestudent = 'student_position_t_' . $matchedvalue;

            $teamniostudentrole = $ssopluginconfig->$tcolnamestudent;

            if (!empty($teamniostudentrole)) {
                $teamniorole = $teamniostudentrole;
            }

            $usertype = 'student';
        } else {
            $teachernumcombinationsval = $ssopluginconfig->teachernumcombination;

            $teacherdbsetarr = array();

            for ($si = 1; $teachernumcombinationsval >= $si; $si++) {
                $teacherpositionmoodle = 'teacher_position_moodle_' . $si;

                $mteacherrole = $ssopluginconfig->$teacherpositionmoodle;

                $teacherinstitution = 'teacher_institution_' . $si;

                $mteacherinstitution = $ssopluginconfig->$teacherinstitution;

                $teacherdepartment = 'teacher_department_' . $si;

                $mteacherdepartment = $ssopluginconfig->$teacherdepartment;

                $teacherdegree = 'teacher_degree_' . $si;

                $mteacherdegree = $ssopluginconfig->$teacherdegree;

                $teacherdbsetarr[$si] = $mteacherrole . "_" . $mteacherinstitution . "_" . $mteacherdepartment . "_" . $mteacherdegree;
            }

            $userteacherinfo = $roleid . "_" . $userinstitution . "_" . $userdepartment . "_" . $userdegreename;

            $matchedvalueteacher = array_search($userteacherinfo, $teacherdbsetarr);

            if ($matchedvalueteacher) {
                $tcolnameteacher = 'teacher_position_t_' . $matchedvalueteacher;

                $teamnioteacherrole = $ssopluginconfig->$tcolnameteacher;

                if (!empty($teamnioteacherrole)) {
                    $teamniorole = $teamnioteacherrole;
                }
                $usertype = 'teacher';
            } else {
                $usertype = 'student';
                $teamniorole = $ssopluginconfig->default_student_position;
            }
        }

        if ($usertype == 'student') {
            $cancreateuser = $ssopluginconfig->web_new_user_student;

            $userapproval = $ssopluginconfig->required_aproval_student;

            $userdesignation = $teamniorole;
        } else {

            if ($usertype == 'teacher') {
                $cancreateuser = $ssopluginconfig->web_new_user_teacher;
                $userdesignation = $teamniorole;
                $userapproval = $ssopluginconfig->required_aproval_teacher;
            }
        }

        $configenroll = get_config('enrol_leeloolxp_enroll');
        $liacnsekey = $configenroll->leeloolxp_licensekey;
        $postdata = '&license_key=' . $liacnsekey;
        $url = 'https://leeloolxp.com/api_moodle.php/?action=page_info';
        $curl = new curl;
        $options = array(
            'CURLOPT_RETURNTRANSFER' => true,
            'CURLOPT_HEADER' => false,
            'CURLOPT_POST' => count($postdata),
        );
        if (!$output = $curl->post($url, $postdata, $options)) {
            return true;
        }
        $infoteamnio = json_decode($output);

        if ($infoteamnio->status != 'false') {
            $teamniourl = $infoteamnio->data->install_url;
        } else {

            $teamniourl = '';

            return false;
        }

        $lastlogin = date('Y-m-d h:i:s', $user->lastlogin);
        $fullname = ucfirst($user->firstname) . " " . ucfirst($user->middlename) . " "
        . ucfirst($user->lastname);
        $city = $user->city;
        $country = $user->country;
        $timezone = $user->timezone;
        $skype = $user->skype;
        $idnumber = $user->idnumber;
        $institution = $user->institution;
        $department = $user->department;
        $phone = $user->phone1;
        $moodlephone = $user->phone2;
        $adress = $user->adress;
        $firstaccess = $user->firstaccess;
        $lastaccess = $user->lastaccess;
        $lastlogin = $lastlogin;
        $lastip = $user->lastip;
        $description = $user->description;
        $descriptionofpic = $user->imagealt;
        $alternatename = $user->alternatename;
        $webpage = $user->url;
        $moodleurlpic = new moodle_url('/user/pix.php/' . $user->id . '/f1.jpg');
        $moodlepicdata = file_get_contents($moodleurlpic);
        $postdata = '&email=' . $user->email . '&username=' . $user->username . '&fullname=' . $fullname . "&courseid=" . $enrolmentdata->courseid . '&designation=' . $userdesignation . "&user_role=" . $teamniorole . "&user_approval=" . $user_approval . "&can_user_create=" . $can_create_user . "&user_type=" . $usertype . "&city=" . $city . "&country=" . $country . "&timezone=" . $timezone . "&skype=" . $skype . "&idnumber=" . $idnumber . "&institution=" . $institution . "&department=" . $department . "&phone=" . $phone . "&moodle_phone=" . $moodlephone . "&adress=" . $adress . "&firstaccess=" . $firstaccess . "&lastaccess=" . $lastaccess . "&lastlogin=" . $lastlogin . "&lastip=" . $lastip . "&user_profile_pic=" . urlencode($moodlepicdata) . "&user_description=" . $description . "&picture_description=" . $descriptionofpic . "&institution=" . $institution . "&alternate_name=" . $alternatename . "&web_page=" . $webpage;

        $url = $teamnio_url . '/admin/sync_moodle_course/enrolment_newuser';
        $curl = new curl;
        $options = array(
            'CURLOPT_RETURNTRANSFER' => true,
            'CURLOPT_HEADER' => false,
            'CURLOPT_POST' => count($postdata),
        );
        if (!$output = $curl->post($url, $postdata, $options)) {
            return true;
        }
    }
}