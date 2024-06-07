<?php
    // Engine
    class SessionManager
    {
        static $mysqli;
        public static function InsertDataBase($database)
        {
            self::$mysqli = $database;
        }
        public static function CreateSession($user, $timelife, $rights, $solt)
        {
            $result['hash'] = hash('sha256', $user."|".$timelife."|".$rights."|".$solt."|".time());
            $sql = "INSERT INTO sessions (`id`,`hash`,`user`,`rights`,`starttime`,`endtime`,`timelife`) VALUES (NULL,'".$result['hash']."','".$user."',$rights,".time().",".(time()+$timelife).",$timelife)";
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
        public static function UpdateSession($id)
        {
            $sql = "UPDATE sessions SET endtime=".time()."+timelife WHERE id=$id";
            self::$mysqli->query($sql);
        }
        public static function SessionSetRights($id, $rights)
        {
            $sql = "UPDATE a_sessions SET rights=$rights WHERE id=$id";
            self::$mysqli->query($sql);
        }
        public static function SessionSetTimelife($id, $timelife)
        {
            $sql = "UPDATE a_sessions SET timelife=$timelife WHERE id=$id";
            self::$mysqli->query($sql);
        }
    }
    // Custom code
    abstract class SessionRights
    {
        const NotAuth           = 0b00000000;
        const ResetPassword     = 0b00000001;
        const AccessPublicInfo  = 0b00000010;
        const FullRights        = 0b11111110;
    }
?>