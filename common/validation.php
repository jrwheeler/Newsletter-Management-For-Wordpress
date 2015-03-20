<?php
/**
 * Base validation of rules. Each rule is based upon
 * the first four letters in the name of the HTML input
 * and as a result the key in a POST array. Used to be 
 * extended by another class which has custom logic to
 * use these regexes.
 */
class ValidationRules {

    /**
     * Checks date to determin if there is a - between
     * all date portions. Then runs php checkdate on
     * all the components. 
     * @param  string $in date in formate YYYY-MM-DD
     * @return boolean     Returns true or false if date passes.
     */
    public function date($in) {
        if (substr_count($in, '-') !== 2) {
            return false;
        }
        list($y, $m, $d) = explode('-', $in);

        return (boolean) checkdate($m, $d, $y);
    }

    /**
     * Checks that only letters exist in string.
     * @param  string $in value to be checked
     * @return boolean     Returns true or false if ony letters
     * are present.
     */
    public function alph($in) {
        $pattern = '^[a-zA-Z\s]+$';
        return (boolean) preg_match("/" . $pattern . "/i", $in);
    }

    /**
     * Checks that only numbers exist in string.
     * @param  string $in value to be ckecked
     * @return boolean     Returns true or false if only numbers
     * are present.
     */
    public function numb($in) {
        $pattern = '^[0-9\s]+$';
        return (boolean) preg_match("/" . $pattern . "/i", $in);
    }

    /**
     * Checks that only numbers and letters exist in string.
     * @param  string $in value to be ckecked
     * @return boolean     Returns true or false if only numbers
     * and letters are present.
     */
    public function alnu($in) {
        $pattern = '[[:alnum:]]';
        return (boolean) preg_match("/" . $pattern . "/i", $in);
    }

    /**
     * Checks that a field is empty. Used to make sure bots don't
     * submit form.
     * @param  string $in value to be ckecked
     * @return boolean     Returns true or false if $in is empty.
     */
    public function bots($in) {
        return (boolean) empty($in);
    }

    /**
     * Checks an email address.
     * @param  string $in value to be ckecked
     * @return boolean     Returns true or false if it is a valid
     * email address.
     */
    public function mail($in){
        return (boolean) filter_var($in, FILTER_VALIDATE_EMAIL);
    }

    /**
     * Checks a phone number. It will replace spaces and periods
     * with dashes before it checks. This is done for simpler
     * checking.
     * @param  string $in value to be ckecked
     * @return boolean     Returns true or false if valid phone
     * number.
     */
    public function phne($in) {
        $in = preg_replace('/[\s.]/', '-', $in);
        $pattern = '^\d{3}-\d{3}-\d{3}';
        return (boolean) preg_match("/" . $pattern . "/i", $in);
    }

    /**
     * Checks an address. Only checks for valid characters. Does
     * nothing to actually check if the address exists.
     * @param  string $in value to be ckecked
     * @return boolean     Returns true or false if only numbers,
     * numbers and characters present.
     */
    public function addy($in) {
        $pattern = '^[\sa-zA-Z0-9:._-]+$';
        return (boolean) preg_match("/" . $pattern . "/i", $in);
    }

}