<?php
require plugin_dir_path( __FILE__ ) . '../../common/validation.php';

/**
 * Custom validation class that extends validation rules.
 * Parent class are regex rules depending on type of
 * validation required. If the first two letters are RQ
 * then it will be a required field. The next four determine
 * the type of validation required. Once instantiated this
 * class will automatically run validation.
 */
class McKenzieTownValidation  extends ValidationRules {
    protected $required = array();
    protected $notRequired = array();
    protected $validationNiceness = array();
    protected $validationErrors = array();
    public    $filteredKeys = array();
    public    $validationResults = array(
                  'required'   => array(),
                  'failed'   => array()
              );

    /**
     * Three arrays are requried to instantiate this class. Values
     * are automatically spilt into required and not required. Both
     * being assinged to different arrays. Then validation is
     * automatically ran on the requried then no required fields.
     * 
     * @param array $toBeValidated      POST array of data to be
     * validated
     * @param array $validationNiceness Associative array of names
     * with keys matching $toBeValidated. When a failed validation
     * occures these values represent the names of the field to be
     * returned as errored. 
     * @param array $validationErrors   Associative array of names
     * with keys matching $toBeValidated. When a failed validation
     * occures these values represent error details. As the parent
     * class currently only returns true or false.
     */
    public function __construct(array $toBeValidated, array $validationNiceness, array $validationErrors) {

        $this->validationNiceness = $validationNiceness;
        $this->validationErrors = $validationErrors;

        foreach ($toBeValidated as $key => $value) {
            if (strpos($key, 'RQ') !== false) {
                $this->required[$key] = $value;
                $this->filteredKeys[substr($key, 6)] = $value;
            } else {
                $this->notRequired[$key] = $value;
                $this->filteredKeys[substr($key, 4)] = $value;
            }
        }

        $this->validateRequired();
        $this->validateNotRequired();

    }
    
    /**
     * Runs the actual validation beased upon the key and the
     * first 4-6 characters. It only knows the current key and
     * value. It will run a function and if the result is not
     * false it will add a multidimentional array with the key
     * failed to the validationResults array.
     * @param  string  $key      the key representing the name
     * of the html element Also it is the key of the array.
     * @param  string  $value    The value from the $toBeValidated
     * array that was constructed. It represents the user's input.
     * @param  boolean $required Is determined if hte field should
     * be required. This is used to determine at what postion the
     * string should be cut. As the string will or will not start
     * with RQ
     * @return null            This function returns nothing.
     */
    private function checkValues($key, $value, $required=false) {

        if ($required) {
            $startPosition = 2;
            $chopoff = 4;
        } else {
            $startPosition = 0;
            $chopoff = 4;
        }
        
        if ($this->{substr($key,$startPosition,$chopoff)}($value) === false) {
             $this->validationResults['failed'][$key] = array($this->validationNiceness[$key].$this->validationErrors[$key]);
        }
    }

    /**
     * Run validation on required fields. It first runs the
     * function checkValues and passes the key, value and
     * requried to it. If the field is empty add a multidimentional
     * array with the key required to the validationResults array.
     * @return null This funciton returns nothing.
     */
    private function validateRequired() {
        foreach ($this->required as $key => $value) {
            $this->checkValues($key, $value, true);   
            if (empty($value)) {
                $this->validationResults['required'][$key] = array($this->validationNiceness[$key]. ' cannot be empty.');
            }
        }

    }

    /**
     * Run validation on non-required fields. It looks to see
     * if there is a value in to be checked. Passing a empty
     * value to be evualated will cause validation to fail.
     * @return null This funciton returns nothing.
     */
    private function validateNotRequired() {
        foreach ($this->notRequired as $key => $value) {
            if (!empty($value)) {
                $this->checkValues($key, $value);
            }
        }
    }

    /** 
     * Returns the results of the validation. This function must
     * be called to get the results. It mereges the required and
     * failed validation results based upon the keys of $toBeValidated. 
     * @return array Returns all validation errors.
     */
    public function returnValidatedResults() {
        return (array) array_merge_recursive($this->validationResults['required'], $this->validationResults['failed']);
    }
}