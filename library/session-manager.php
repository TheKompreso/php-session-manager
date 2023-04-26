<?php
    class SessionManager
    {
        static $mysqli;
        public static function InsertDataBase($database)
        {
            self::$mysqli = $database;
        }
        public static function CreateSession($user, $timelife, $rights, $solt)
        {
            $result['key'] = hash('sha256', $user."|".$timelife."|".$rights."|".$solt."|".time());
            $sql = "INSERT INTO sessions (`id`,`hash`,`user`,`rights`,`starttime`,`endtime`) VALUES (NULL,'".$result['key']."','".$user."',$rights,".time().",".(time()+$timelife).")";
            if (self::$mysqli->query($sql) !== false) 
            {
                $result['id'] = self::$mysqli->insert_id;
                return $result;
            } 
            else 
            {
                echo "error: " . self::$mysqli->error;
                exit;
            }
        }
        public static function GetSession($id, $key)
        {
            $sql = "SELECT * FROM sessions WHERE id='".(int)$id."' LIMIT 1";
            $result = self::$mysqli->query($sql);
            if($result !== false) // Ошибка запроса
            {
                if($data = $result->fetch_assoc()) // Сессия найдена
                {
                    if($data['endtime'] < time()) return null;
                    if(strcmp($data['hash'],$key) !== 0) return null;
                    unset($data['hash']);
                    return $data;
                }
            }
            return null;
        }
        public static function EndSession($id)
        {
            $sql = "UPDATE sessions SET endtime=".time()." WHERE id=$id";
            self::$mysqli->query($sql);
        }
    }
?>