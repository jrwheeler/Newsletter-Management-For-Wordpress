<?php

require plugin_dir_path( __FILE__ ) . 'inc/mckenzie-validation.php';
require plugin_dir_path( __FILE__ ) . 'inc/database.php';
require plugin_dir_path( __FILE__ ) . '../common/age.php';
require plugin_dir_path( __FILE__ ) . '../assets/vendors/mailchimp/Mailchimp.php';

if ( ! defined( 'WPINC' ) ) {
    die;
}
/**
 * Main controller for public side of plugin.
 * No constuctor required.
 * Each function is used in mckenzie-signup class. 
 */
class McKenzieTownPublicController {
    private $apiKey = '';
    private $listID = '';

    /**
     * Load any JS and CSS scripts. Included are built in scripts and any custom work
     * @return Null nothing is returned
     */
    public function loadScripts() {

        wp_enqueue_style('signupStyle', plugin_dir_url( __FILE__ ) . 'assets/css/signup.css', array('front-all')); 
        // wp_enqueue_style('signupStyle', plugin_dir_url( __FILE__ ) . 'assets/css/signup.css'); 
        wp_enqueue_style('jqueryUIAutocomplete', '//cdnjs.cloudflare.com/ajax/libs/jqueryui/1.10.4/css/jquery-ui.min.css'); 

        wp_enqueue_script("jquery-ui-autocomplete");
        wp_enqueue_script('signup', plugin_dir_url( __FILE__ ) . 'assets/js/signup.js', array('jquery', 'jquery-ui-autocomplete')); 
        wp_localize_script( 'signup', 'submitMailchimp',
            array( 
                'url' => admin_url( 'admin-ajax.php'),
                'nonce' => wp_create_nonce( "signup_nonce" ) 
            )
        );
    }

    /**
     * Called by shortcode mckenzieMailChimpForm returns static html. Form for signing up through mailchimp
     * @return Null nothing is returned
     */
    public function loadForm() {
        $form = <<<EOT
            <form class="signUpForm _signUpForm">
                <div class="_messageContaier"></div>
                <input type="text" placeholder="First Name" name="RQalphfirstName" />
                <input type="text" placeholder="Last Name" name="RQalphlastName" />
                <input type="text" placeholder="Address" name="addyaddress" />
                <input type="text" placeholder="Address 2" name="addyaddressTwo" />
                <input type="text" placeholder="City" name="RQalphcity" />
                <input type="text" placeholder="Postal Code" name="alnupostalCode" />
                <input type="text" placeholder="Email" name="RQmailemail" />
                <input type="text" placeholder="Phone Number" name="phnephone" />
                <input type="text" placeholder="Cell Number" name="phnecell" />
                <div class="entrySelectorWrapper _entrySelectorWrapper" >
                    <input type="radio" name="alphtypeOfEntry" value="parent" id="parentEntry" class="_entrySelector" /><label for="parentEntry">Parent</label> 
                </div>
                <div class="entrySelectorWrapper _entrySelectorWrapper" >
                    <input type="radio" name="alphtypeOfEntry" value="student" id="studentEntry" class="_entrySelector" /><label for="studentEntry">Student</label> 
                </div>

                <input type="text" id="check" placeholder="If you see this form element leave blank" name="botscheck" />
                <input type="button" value="Submit" />

            </form>
EOT;
        return $form;
    }

    /**
     * Creates new instance of database class.
     * Finds all schools within database table tblSchools
     * @return JSON Returns sucess with results in array and a success message
     */
    public function getSchools() {
        $databaseInteraction = new McKenzieTownDatabase();
        wp_send_json_success($databaseInteraction->findAllSchools());
    }

    /**
     * Most of the mailchimp magic happens in this function.
     * Requests are received through AJAX post request, this function is never
     * accessable outside of AJAX requests
     * 
     * Validatation is instantiated three arrays are pasted into the class.
     * The class returns a result of all passed or not.
     * If it does not pass a multidimentional array is returned
     * with the key being the HTML parameter of name. 
     * Jquery is then used to highlight errors based upon the key.
     *
     * If validation is passed, data is added to the database.
     * The POST data is checked to determine age and grade to be submitted
     * to mailchimp.
     *
     * Using the mailchimp api the user is either created or updated based
     * upon email. Information from POST request and age group are sent to
     * mailchimp to be stored in two groups (Grade and Youth Group).
     *
     * If success a JSON success message is sent to the browser. On failure
     * JSON is returned with an error message and all failed validation 
     * responses.
     *
     * @todo  Move out logic to seperate classes. Including making a mailchimp
     * class.
     * 
     * @return JSON Success or fail message with validation errors
     */
    public function mailChimpAjax() {
        // Remove action and nonce required for wordpress
        unset($_POST['action']);
        unset($_POST['nonce']);
        
        $validationNiceness = array(
            "RQalphfirstName"       => 'First Name',
            "RQalphlastName"        => 'Last Name',
            "addyaddress"           => 'Address',
            "addyaddressTwo"        => 'Address 2',
            "RQalphcity"            => 'City',
            "alnupostalCode"        => 'Postal',
            "RQmailemail"           => 'Email',
            "phnephone"             => 'Phone',
            "phnecell"              => 'Cell',
            "datebirthday"          => 'Birthday',
            "alphschool"            => 'School',
            "alphparentFirstName"   => 'Parents\'s First Name',
            "alphparentLastName"    => 'Parents\'s Last Name',
            "mailparentEmail"       => 'Parents\'s Email',
            "phneparentPhone"       => 'Parents\'s Phone'
            );

        $validationErrors = array(
            "RQalphfirstName"       => ' must be letters only.',
            "RQalphlastName"        => ' must be letters only.',
            "addyaddress"           => ' must be numbers and letters only.',
            "addyaddressTwo"        => ' must be numbers and letters only.',
            "RQalphcity"            => ' must be letters only.',
            "alnupostalCode"        => ' must be numbers and letters only.',
            "RQmailemail"           => ' must be a valid email.',
            "phnephone"             => ' must be in format 555-555-5555.',
            "phnecell"              => ' must be in format 555-555-5555.',
            "datebirthday"          => ' must be in format YYYY-MM-DD.',
            "alphschool"            => ' must be numbers and letters only.',
            "alphparentFirstName"   => ' must be letters only.',
            "alphparentLastName"    => ' must be letters only.',
            "mailparentEmail"       => ' must be a valid email.',
            "phneparentPhone"       => ' must be in format 555-555-5555.'
            );

        $validation = new McKenzieTownValidation($_POST, $validationNiceness, $validationErrors);
        $validationResults = $validation->returnValidatedResults();
        
        // Validation passed
        if (!empty($validationResults)) {
            wp_send_json_error($validationResults);
        } else {
            $databaseInteraction = new McKenzieTownDatabase();
            $userAlreadyExists = $databaseInteraction->doesTheUserAlreadyExist($validation->filteredKeys['email']);
            if ($databaseInteraction->addMemeberToDataBase($validation->filteredKeys)) {
                $ageUtility = new McKenzieTownAge($validation->filteredKeys['birthday']);
                if (isset($validation->filteredKeys['birthday']) && $validation->filteredKeys['typeOfEntry'] === 'student') {
                    $age = $ageUtility->determineAge();
                    $grade = $ageUtility->determineGradeByAge();
                    $group = $ageUtility->findGradeGroup();
                    if ($age >= 18 ) {
                        $group = 'Graduates';
                    }
                } elseif ($validation->filteredKeys['typeOfEntry'] === 'parent') {
                        $group = 'Parents';
                }

                // Youth = 5281
                // Grade = 5285

                $mailChimp = new Mailchimp($this->apiKey);
                if ($group !== 'Parents') {
                    $mergeVar = array(
                        'fname'     => $validation->filteredKeys['firstName'],
                        'lname'     => $validation->filteredKeys['lastName'],
                        'new-email' => $validation->filteredKeys['email'],
                        'groupings' => array(
                            array( 
                                'id'     => 5281,
                                'groups' => array($group)
                                ),
                            array(
                                'id'     => 5285,
                                'groups' => array('Grade '.$grade)
                                )
                            ),
                        'mc_language' => 'English'
                        );
                } else {
                    $mergeVar = array(
                        'fname'     => $validation->filteredKeys['firstName'],
                        'lname'     => $validation->filteredKeys['lastName'],
                        'new-email' => $validation->filteredKeys['email'],
                        'groupings' => array(
                            array( 
                                'id'     => 5281,
                                'groups' => array($group)
                                )
                            ),
                        'mc_language' => 'English'
                        );                    
                }

                $email = array( 'email' => $validation->filteredKeys['email'] );
                    
                try {
                    if ($userAlreadyExists) {
                        $subscribeMe = $mailChimp->lists->subscribe($this->listID, $email, $mergeVar, 'html', false, true, true);
                    } else {
                        $subscribeMe = $mailChimp->lists->subscribe($this->listID, $email, $mergeVar, 'html', false);
                    }
                } catch (Exception $e) {
                    wp_send_json_error('There was an error please contact James.');
                }
                wp_send_json_success();
            }
        }

        die();
    }


}