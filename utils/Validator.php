<?php
class Validator {

    public static function validateEmail($value) {

        if(empty($value)) {
            throw new Exception('Validator: Error Field Required Email');
        }
        elseif(!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Validator: Error Field Email Invalid');
        }
        return $value;
    }

    public static function validateRequired($key, $value) {

        if(empty($value)) {
            throw new Exception('Validator: Error Field Required '.$key);
        }
        return $value;
    }

    public static function validateBool($key, $value) {

        if(!is_bool($value)){
            throw new Exception('Validator: Error Field is not Boolean '.$key);
        }
        return $value;
    }

    public static function validateInteger($key, $value) {

        if(!is_int($value)){
            throw new Exception('Validator: Error Field is not Integer '.$key. '= '.$value);
        }
        return $value;
    }

    public function validateRequiredFields($inputData, $requiredFields) {
        foreach ($requiredFields as $field) {
            if (!isset($inputData[$field]) || empty($inputData[$field])) {
                throw new Exception('The field '.$field.' is required and is not provided.');
        }
    }
}

}
