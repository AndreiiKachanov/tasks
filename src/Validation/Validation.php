<?php

namespace App\Validation;

/**
 * Validation module
 *
 */
class Validation
{
    // Table name
    private string $table;
    // Rules loaded from rules_map.php
    private array $rules;
    // Array of validation errors constants
    private array $errors;
    // Array formed from the input data with cleaned and validated values
    private array $finalObject;
    // Path to the file with messages
    private string $messagesPath;
    // Path to the file with rules
    private string $rulesPath;

    public function __construct(string $table)
    {
        $this->table = $table;
        $this->rulesPath = RULES_PATH;
        $this->messagesPath = MESSAGES_PATH;
        $this->rules = $this->loadRules();
        $this->errors = [];
        $this->finalObject = [];
    }

    /**
     * Execute validation on input data
     *
     * @param $obj - array from form. Keys represent fields in the database, values represent data
     * @return void
     */
    public function execute($obj): void
    {
        $cleanObj = [];
        foreach ($obj as $key => $value) {
            if (in_array($key, $this->rules['fields'])) {
                if (!is_array($value)) {
                    $value = $value ?? '';
                    $count = iconv_strlen($value, 'UTF-8');
                }
                if (
                    array_key_exists('not_empty', $this->rules) &&
                    in_array($key, $this->rules['not_empty'])
                ) {
                    if ($value === '') {
                        $this->errors[] = ['not_empty', $key];
                    } else {
                        if (
                            array_key_exists('range', $this->rules) &&
                            array_key_exists($key, $this->rules['range']) &&
                            ($count < $this->rules['range'][$key][0] ||
                                $count > $this->rules['range'][$key][1])
                        ) {
                            $this->errors[] = ['range', $key,
                                               $this->rules['range'][$key][0],
                                               $this->rules['range'][$key][1]];
                        } elseif (
                            array_key_exists('nickname', $this->rules) &&
                            in_array($key, $this->rules['nickname']) &&
                            !$this->nickname($value)
                        ) {
                            $this->errors[] = ['nickname', $key];
                        } elseif (
                            array_key_exists('email', $this->rules) &&
                            in_array($key, $this->rules['email']) &&
                            !$this->email($value)
                        ) {
                            $this->errors[] = ['email', $key];
                        }
                    }
                }
                $cleanObj[] = [$key => $value];
            }
        }

        foreach ($cleanObj as $key) {
            foreach ($key as $oneKey => $value) {
                $this->finalObject[$oneKey] = $value;
            }
        }
    }


    /**
     * Validate email address
     *
     * @param string $email
     * @param $strict
     * @return bool
     */
    private function email(string $email, $strict = false): bool
    {
        if (strlen($email) > 254) {
            return false;
        }

        if ($strict === true) {
            $qtext = '[^\\x0d\\x22\\x5c\\x80-\\xff]';
            $dtext = '[^\\x0d\\x5b-\\x5d\\x80-\\xff]';
            $atom  = '[^\\x00-\\x20\\x22\\x28\\x29\\x2c\\x2e\\x3a-\\x3c\\x3e\\x40\\x5b-\\x5d\\x7f-\\xff]+';
            $pair  = '\\x5c[\\x00-\\x7f]';
            $domain_literal = "\\x5b($dtext|$pair)*\\x5d";
            $quoted_string  = "\\x22($qtext|$pair)*\\x22";
            $sub_domain     = "($atom|$domain_literal)";
            $word           = "($atom|$quoted_string)";
            $domain         = "$sub_domain(\\x2e$sub_domain)*";
            $local_part     = "$word(\\x2e$word)*";
            $expression     = "/^$local_part\\x40$domain$/D";
        } else {
            $expression = '/^[-_a-z0-9\'+*$^&%=~!?{}]++(?:\.[-_a-z0-9\'+*$^&%=~!?{}]+)*+@(?:(?![-.])[-a-z0-9.]+(?<![-.])\.[a-z]{2,6}|\d{1,3}(?:\.\d{1,3}){3})$/iD';
        }

        return (bool) preg_match($expression, $email);
    }

    /**
     * Validate nickname
     *
     * @param string $author
     * @return bool
     */
    private function nickname(string $author): bool
    {
        $expression = '/^[a-zA-ZА-я0-9_.-]+$/u';
        // Проверяем наличие разрешенных символов и возвращаем результат как булево значение
        return (bool) preg_match($expression, $author);
    }


    /**
     * Check if validation is successful
     *
     * @return bool
     */
    public function good(): bool
    {
        return count($this->errors) === 0;
    }

    /**
     * Get validated and sanitized object fields for database
     *
     * @return array
     */
    public function getObj(): array
    {
        return (count($this->errors) === 0) ? $this->finalObject : [];
    }

    /**
     * Get errors array
     * @return array
     */
    private function getErrors(): mixed
    {
        $errors[] = $this->errors;
        return $errors[0];
    }


    /**
     * Get errors with corresponding messages
     *
     * @return array
     */
    public function errors(): array
    {
        $errorsWithMessages = [];
        $messages = include $this->messagesPath;
        $labels = $this->rules['labels'];
        $errors = $this->getErrors();
        foreach ($errors as $i) {
            if (isset($i[1])) {
                $message = str_replace(':label_1', $labels[$i[1]], $messages[$i[0]]);
            }

            if (isset($i[2])) {
                if (is_numeric($i[2])) {
                    $message = str_replace(':param_1', $i[2], $message);
                } else {
                    $message = str_replace(':label_2', $labels[$i[2]], $message);
                }
            }
            if (isset($i[3])) {
                $message = str_replace(':param_2', $i[3], $message);
            }

            $errorsWithMessages[] = $message;
        }

        return $errorsWithMessages;
    }


    /**
     * Load rules based on table name from rules_map.php
     *
     * @return mixed
     */
    private function loadRules(): mixed
    {
        $rules = include $this->rulesPath;
        return $rules[$this->table];
    }
}
