<?php

if ( ! defined( 'WPINC' ) ) {
    die;
}

/**
 * Database interaction class. This class is used for 
 * all public facing database queires
 * Constructor places global wordpress database obeject into
 * a private variable for easy access in the class
 */
class McKenzieTownDatabase {
    private $wpdb;
    public function __construct() {
        global $wpdb;
        $this->wpdb = $wpdb;

    }

    /**
     * Adds a user to the database. Uses unique keys in the
     * database to determine if insert or update is required.
     * The unique key is the email address.
     * 
     * @param array $member Associative array of data to be
     * inserted to the database.
     *
     * @return boolean If the the database successfully inserts the data
     */
    public function addMemeberToDataBase(array $member) {
        $addMemberQry = $this->wpdb->prepare("INSERT INTO tblMailChimp 
            (firstName, lastName, address, addressTwo,
                city, province, postalCode, email,
                phone, cell, typeOfEntry, birthday,
                school, parentFirstName, parentLastName, parentEmail,
                parentPhone, dateCreated, dateUpdated) 
            VALUES
            ('%s', '%s', '%s', '%s',
                '%s', 'Alberta', '%s', '%s',
                '%s', '%s', '%s', '%s',
                '%s', '%s', '%s', '%s',
                '%s', NOW(), NOW())
            ON DUPLICATE KEY UPDATE
            firstName = '%s', lastName = '%s', address = '%s', addressTwo = '%s',
                city = '%s', province = 'Alberta', postalCode = '%s',
                phone = '%s', cell = '%s', typeOfEntry = '%s', birthday = '%s',
                school = '%s', parentFirstName = '%s', parentLastName = '%s', parentEmail = '%s',
                parentPhone = '%s', dateUpdated = NOW()",
            $member['firstName'], $member['lastName'], $member['address'], $member['addressTwo'],
            $member['city'], $member['postalCode'], $member['email'], $member['phone'],
            $member['cell'], $member['typeOfEntry'], $member['birthday'], $member['school'],
            $member['parentFirstName'], $member['parentLastName'], $member['parentEmail'], $member['parentPhone'],
            $member['firstName'], $member['lastName'], $member['address'], $member['addressTwo'],
            $member['city'], $member['postalCode'], $member['phone'], $member['cell'],
            $member['typeOfEntry'], $member['birthday'], $member['school'], $member['parentFirstName'],
            $member['parentLastName'], $member['parentEmail'], $member['parentPhone']);

        $addMemberRS = $this->wpdb->query($addMemberQry);
        if ($addMemberRS !== 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Checks if a user is found in the database
     * 
     * @param  string $email Email address
     * 
     * @return boolean        Returns true if there is a user 
     * and false if no user is found.
     */
    public function doesTheUserAlreadyExist($email) {
        $checkForMemberQry = $this->wpdb->prepare("SELECT email FROM tblMailChimp WHERE email = '%s'",$email);

        $checkForMemberRS = $this->wpdb->query($checkForMemberQry);

        if ($checkForMemberRS !== 0) {
            return true;
        } else {
            return false;
        }

    }

    /**
     * Return a list of all schools found in the database.
     * @return array return a numerative array of schools.
     */
    public function findAllSchools() {
        $findSchoolsRS = $this->wpdb->get_results('SELECT schoolName FROM tblSchools ORDER BY schoolName' ,ARRAY_N);
        $schools = array();
        foreach ($findSchoolsRS as $key => $value) {
            array_push($schools, $value[0]);
        }
        return $schools;
    }

}