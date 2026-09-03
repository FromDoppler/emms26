<?php
class DB
{

    protected $connection;
    public $query;
    protected $show_errors = TRUE;
    protected $query_closed = TRUE;
    public $query_count = 0;
    private $lastErrno = 0;
    private $lastError = '';

    public function __construct($dbhost, $dbuser, $dbpass, $dbname, $charset = 'utf8mb4')
    {
        $this->connection = new mysqli($dbhost, $dbuser, $dbpass, $dbname);
        if ($this->connection->connect_error) {
            $this->error('Failed to connect to MySQL - ' . $this->connection->connect_error);
        }
        $this->connection->set_charset($charset);
    }

    public function query($query)
    {
        $this->lastErrno = 0;
        $this->lastError = '';

        if (!$this->query_closed) {
            $this->query->close();
            $this->query_closed = TRUE;
        }
        $this->query = null;

        try {
            if ($this->query = $this->connection->prepare($query)) {
                $this->query_closed = FALSE;

                if (func_num_args() > 1) {
                    $x = func_get_args();
                    $args = array_slice($x, 1);
                    $types = '';
                    $args_ref = array();
                    foreach ($args as $k => &$arg) {
                        if (is_array($args[$k])) {
                            foreach ($args[$k] as $j => &$a) {
                                $types .= $this->_gettype($args[$k][$j]);
                                $args_ref[] = &$a;
                            }
                        } else {
                            $types .= $this->_gettype($args[$k]);
                            $args_ref[] = &$arg;
                        }
                    }
                    array_unshift($args_ref, $types);
                    call_user_func_array(array($this->query, 'bind_param'), $args_ref);
                }

                $executed = $this->query->execute();
                if (!$executed || $this->query->errno) {
                    $this->lastErrno = (int) $this->query->errno;
                    $this->lastError = (string) $this->query->error;
                    $this->error('Unable to process MySQL query (check your params) - ' . $this->query->error);
                }

                $this->query_count++;
            } else {
                $this->lastErrno = (int) $this->connection->errno;
                $this->lastError = (string) $this->connection->error;
                $this->error('Unable to prepare MySQL statement (check your syntax) - ' . $this->connection->error);
            }
        } catch (mysqli_sql_exception $error) {
            $this->lastErrno = (int) $error->getCode();
            $this->lastError = trim((string) $error->getMessage());

            if ($this->lastErrno === 0) {
                $this->lastErrno = $this->query instanceof mysqli_stmt
                    ? (int) $this->query->errno
                    : (int) $this->connection->errno;
            }
            if ($this->lastError === '') {
                $this->lastError = $this->query instanceof mysqli_stmt
                    ? (string) $this->query->error
                    : (string) $this->connection->error;
            }

            throw $error;
        }

        return $this;
    }

    public function fetchAll($callback = null)
    {
        $params = array();
        $row = array();
        $meta = $this->query->result_metadata();
        while ($field = $meta->fetch_field()) {
            $params[] = &$row[$field->name];
        }
        call_user_func_array(array($this->query, 'bind_result'), $params);
        $result = array();
        while ($this->query->fetch()) {
            $r = array();
            foreach ($row as $key => $val) {
                $r[$key] = $val;
            }
            if ($callback != null && is_callable($callback)) {
                $value = call_user_func($callback, $r);
                if ($value == 'break') break;
            } else {
                $result[] = $r;
            }
        }
        $this->query->close();
        $this->query_closed = TRUE;
        return $result;
    }

    public function fetchArray()
    {
        $params = array();
        $row = array();
        $meta = $this->query->result_metadata();
        while ($field = $meta->fetch_field()) {
            $params[] = &$row[$field->name];
        }
        call_user_func_array(array($this->query, 'bind_result'), $params);
        $result = array();
        while ($this->query->fetch()) {
            foreach ($row as $key => $val) {
                $result[$key] = $val;
            }
        }
        $this->query->close();
        $this->query_closed = TRUE;
        return $result;
    }

    public function close()
    {
        return $this->connection->close();
    }

    public function beginTransaction()
    {
        $result = $this->connection->begin_transaction();
        if (!$result) {
            $this->lastErrno = (int) $this->connection->errno;
            $this->lastError = (string) $this->connection->error;
            $this->error('Unable to begin MySQL transaction - ' . $this->lastError);
        } else {
            $this->lastErrno = 0;
            $this->lastError = '';
        }
        return $result;
    }

    public function commit()
    {
        $result = $this->connection->commit();
        if (!$result) {
            $this->lastErrno = (int) $this->connection->errno;
            $this->lastError = (string) $this->connection->error;
            $this->error('Unable to commit MySQL transaction - ' . $this->lastError);
        } else {
            $this->lastErrno = 0;
            $this->lastError = '';
        }
        return $result;
    }

    public function rollback()
    {
        $result = $this->connection->rollback();
        if (!$result) {
            $this->lastErrno = (int) $this->connection->errno;
            $this->lastError = (string) $this->connection->error;
            $this->error('Unable to rollback MySQL transaction - ' . $this->lastError);
        }
        return $result;
    }

    public function numRows()
    {
        $this->query->store_result();
        return $this->query->num_rows;
    }

    public function affectedRows()
    {
        return $this->query->affected_rows;
    }

    public function lastInsertID()
    {
        return $this->connection->insert_id;
    }

    public function lastErrno(): int
    {
        return $this->lastErrno;
    }

    public function lastError(): string
    {
        return $this->lastError;
    }

    public function error($error)
    {
        if ($this->show_errors) {
            throw new Exception('DB: Error ' . $error);
        }
    }

    private function _gettype($var)
    {
        if (is_string($var)) return 's';
        if (is_float($var)) return 'd';
        if (is_int($var)) return 'i';
        return 'b';
    }

    public function insertSubscriptionDoppler($subscription)
    {

        $fields = "(email, list, form_id, register, firstname, phone, company, jobPosition, website, emailPlatform, ecommerce, `digital-trends`,";
        $fields .= "ip, country_ip, privacy, promotions, source_utm, medium_utm, campaign_utm, content_utm, term_utm, emms_ref)";
        date_default_timezone_set('America/Argentina/Buenos_Aires');
        $values = array(
            $subscription['email'],
            $subscription['list'],
            $subscription['form_id'],
            date("Y-m-d H:i:s"),
            $subscription['firstname'],
            $subscription['phone'],
            $subscription['company'],
            $subscription['jobPosition'],
            $subscription['website'],
            $subscription['emailPlatform'],
            $subscription['ecommerce'],
            $subscription['digital_trends'],
            $subscription['ip'],
            $subscription['country_ip'],
            intval($subscription['privacy']),
            intval($subscription['promotions']),
            $subscription['source_utm'],
            $subscription['medium_utm'],
            $subscription['campaign_utm'],
            $subscription['content_utm'],
            $subscription['term_utm'],
            $subscription['emms_ref']
        );
        $this->query("INSERT INTO subscriptions_doppler $fields VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)", $values);
    }

    public function insertSubscriptionErrors($email, $list, $reason, $errorCode)
    {
        $email = $this->connection->real_escape_string($email);
        $list = $this->connection->real_escape_string($list);
        $reason = $this->connection->real_escape_string($reason);
        $errorCode = $this->connection->real_escape_string($errorCode);
        $sql = "INSERT INTO subscription_doppler_list_errors (email, list, reason, error_code) VALUES ('$email', '$list', '$reason', '$errorCode')";
        $this->query($sql);
        $this->query->close();
    }

    public function getSubscriptionsDoppler()
    {
        $sql = $this->query("SELECT * FROM subscriptions_doppler order by id DESC LIMIT 100");
        $result = $sql->fetchAll();
        return $result;
    }

    public function saveRegistered($subscription)
    {
        $email = $this->connection->real_escape_string($subscription['email']);

        $registered = $this->query("SELECT id FROM registered WHERE email=?", [$email]);

        if ($registered->fetchArray()) {
            // Update only non-null or empty fields
            $updateFields = [];
            $updateValues = [];

            // Defines the list of fields in the database
            $dbFields = [
                'phase',
                'firstname',
                'phone',
                'company',
                'jobPosition',
                'website',
                'emailPlatform',
                'ecommerce',
                'digital-trends',
                'source_utm',
                'medium_utm',
                'campaign_utm',
                'content_utm',
                'term_utm',
                'emms_ref',
            ];

            foreach ($dbFields as $field) {
                $sourceField = $field === 'phase'
                    ? 'form_id'
                    : ($field === 'digital-trends' ? 'digital_trends' : $field);

                if (!array_key_exists($sourceField, $subscription)) {
                    continue;
                }

                $value = $subscription[$sourceField];
                if ($value === '' || $value === null) {
                    continue;
                }

                $column = $field === 'digital-trends' ? "`$field`" : $field;
                if ($field === 'ecommerce' || $field === 'digital-trends') {
                    $updateFields[] = "$column = GREATEST($column, ?)";
                    $updateValues[] = (int) $value;
                    continue;
                }

                $updateFields[] = "$column = ?";
                $updateValues[] = $this->connection->real_escape_string($value);
            }

            if (!empty($updateFields)) {
                $updateFields = implode(', ', $updateFields);
                $updateValues[] = $email;

                $this->query("UPDATE registered SET $updateFields WHERE email=?", $updateValues);
            }

        } else {
            $fields = "(`email`, `phase`, `register`, `firstname`, `phone`, `ecommerce`, `digital-trends`, ";
            $fields .= "`source_utm`, `medium_utm`, `campaign_utm`, `content_utm`, `term_utm`, `emms_ref`,";
            $fields .= "`company`, `jobPosition`,`website`, `emailPlatform`)";

            $values = [
                $email,
                $this->connection->real_escape_string($subscription['form_id']),
                $this->connection->real_escape_string($subscription['register']),
                $this->connection->real_escape_string($subscription['firstname']),
                $this->connection->real_escape_string($subscription['phone']),
                $this->connection->real_escape_string($subscription['ecommerce']),
                $this->connection->real_escape_string($subscription['digital_trends']),
                $this->connection->real_escape_string($subscription['source_utm']),
                $this->connection->real_escape_string($subscription['medium_utm']),
                $this->connection->real_escape_string($subscription['campaign_utm']),
                $this->connection->real_escape_string($subscription['content_utm']),
                $this->connection->real_escape_string($subscription['term_utm']),
                $this->connection->real_escape_string($subscription['emms_ref']),
                $this->connection->real_escape_string($subscription['company']),
                $this->connection->real_escape_string($subscription['jobPosition']),
                $this->connection->real_escape_string($subscription['website']),
                $this->connection->real_escape_string($subscription['emailPlatform']),
            ];

            $this->query("INSERT INTO `registered` $fields VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)", $values);
        }
    }

    public function isRegisteredForEvent($email, $eventId)
    {
        $eventColumns = [
            ECOMMERCE     => ['ecommerce', '`ecommerce-vip`'],
            DIGITALTRENDS => ['`digital-trends`', '`digital-trends-vip`'],
        ];

        if (!isset($eventColumns[$eventId])) {
            throw new Exception("Unknown event id: $eventId");
        }

        [$freeCol, $vipCol] = $eventColumns[$eventId];
        $sql = "SELECT 1 FROM registered WHERE email = ? AND ($freeCol = 1 OR $vipCol = 1) LIMIT 1";
        $result = $this->query($sql, [$email])->fetchArray();
        return !empty($result);
    }



    public function google_oauth_is_table_empty()
    {
        $result = $this->query("SELECT id FROM google_oauth WHERE provider = 'google'");
        if ($result->numRows()) {
            return false;
        }
        return true;
    }

    public function google_oauth_get_access_token()
    {
        $sql = $this->query("SELECT provider_value FROM google_oauth WHERE provider = 'google'");
        $result = $sql->fetchArray();
        return json_decode($result['provider_value']);
    }

    public function google_oauth_get_refersh_token()
    {
        $result = $this->google_oauth_get_access_token();
        return $result->refresh_token;
    }

    public function google_oauth_update_access_token($token)
    {
        if ($this->google_oauth_is_table_empty()) {
            $this->query("INSERT INTO google_oauth(provider, provider_value) VALUES('google', '$token')");
        } else {
            $this->query("UPDATE google_oauth SET provider_value = '$token' WHERE provider = 'google'");
        }
    }

    public function getUserNameByEmail($email)
    {
        $sql = $this->query("SELECT firstname FROM registered WHERE email = '$email'");
        $result = $sql->fetchAll();
        return $result;
    }

    /********DATA ABMS*************/
    public function hasActiveSponsor()
    {
        $sql = $this->query("SELECT COUNT(*) as count FROM sponsors WHERE status = '1'");
        $result = $sql->fetchAll();
        return $result[0]['count'] > 0;
    }


    public function getSponsorsByType($type)
    {
        $sql = $this->query("SELECT * FROM sponsors  WHERE status = '1' AND sponsor_type = '$type' ORDER BY priority_home");
        $result = $sql->fetchAll();
        return $result;
    }

    public function getSponsorsCards($type)
    {
        $sql = $this->query("SELECT * FROM sponsors  WHERE status = '1' AND sponsors.visible_card = '1' AND sponsor_type = '$type' ORDER BY priority_card");
        $result = $sql->fetchAll();
        return $result;
    }

    public function getSponsorsBySlug($slug)
    {
        $sql = $this->query("SELECT * FROM sponsors  WHERE status = '1' AND slug = '$slug'");
        $result = $sql->fetchAll();
        return $result;
    }

    public function getAllSpeakers()
    {
        $sql = $this->query("SELECT * FROM speakers  order by orden");
        $result = $sql->fetchAll();
        return $result;
    }

    public function getSpeakersByDay($day)
    {
        $sql = $this->query("SELECT * FROM speakers WHERE day = " . $day . " order by orden");
        $result = $sql->fetchAll();
        return $result;
    }

    public function getSponsors($orden)
    {
        $sql = $this->query("SELECT * FROM aliados_pro order by " . $orden);
        $result = $sql->fetchAll();
        return $result;
    }

    public function getMediaPartnersStarter($orden)
    {
        $sql = $this->query("SELECT * FROM aliados_starter order by " . $orden);
        $result = $sql->fetchAll();
        return $result;
    }

    public function getMediaPartnersExclusive($orden)
    {
        $sql = $this->query("SELECT * FROM aliados_media_partner order by " . $orden);
        $result = $sql->fetchAll();
        return $result;
    }

    public function getAliadoProBySlug($slug)
    {
        $sql = $this->query("SELECT * FROM aliados_pro where slug='" . $slug . "'");
        $result = $sql->fetchAll();
        return $result;
    }
    public function getAliadoStarterBySlug($slug)
    {
        $sql = $this->query("SELECT * FROM aliados_starter where slug='" . $slug . "'");
        $result = $sql->fetchAll();
        return $result;
    }

    public function getSpeakerBySlug($slug)
    {
        $sql = $this->query("SELECT * FROM speakers where slug='" . $slug . "'");
        $result = $sql->fetchAll();
        return $result;
    }
    public function getCurrentPhase($event)
    {

        $sql = $this->query("SELECT * from settings_phase WHERE event='" . $event . "' AND 1=1");
        $result = $sql->fetchAll();
        return $result;
    }

    public function getAllRegistersEMMS()
    {
        $result = $this->query("SELECT * FROM registered WHERE 1=1");
        return $result->fetchAll();
    }

    /******* log errors */
    public function insertLogErrors($date, $functionName, $description, $data)
    {
        $sql = "INSERT INTO log_errors (date, function_name, description, data) values ('" . $date . "', '" . $functionName . "', '" . $description . "', '" . $data . "')";

        $this->query($sql);
    }

    public function getLogErrors()
    {
        $sql = $this->query("SELECT * FROM log_errors order by id DESC LIMIT 100");
        $result = $sql->fetchAll();
        return $result;
    }
}
