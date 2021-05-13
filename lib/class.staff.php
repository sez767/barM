<?php

class Staff {

    public static $_STAFF_SYSTEM_ID = 10000000;
    public static $_STAFF_API_ADMIN_ID = 10000001;
    public static $STAFF_SYSTEM_CRONTAB = 99999999;

    public $Id = 0;
    public $Password = '';
    public $Level = 0;
    public $Type = 0;
    public $FirstName = '';
    public $LastName = '';
    public $Position = '';
    public $Responsible = '';
    public $Birthday = '';
    public $created_at = '';
    public $Skype = '';
    public $Email = '';
    public $Bonuses = 0;
    public $PaymentOption = 0;
    public $PaymentBase = 0;
    public $Ban = 1;
    public $Domain = '';
    public $Phone = '';
    public $Notes = '';
    ///////////////////////////////////////
    public $admin = 0; // Администратор
    public $operator = 0; // Оператор
    public $logist = 0; // Логист
    public $adminlogist = 0; // Админ логистики
    public $postlogist = 0; // Логист Почта
    public $admincity = 0; // Город админ
    public $adminlogistpost = 0; // Админ логистики Почта
    public $logistcity = 0; // Контроль качества
    public $incomeoper = 0; // Оператор входящей
    public $operatorcold = 0; // Холодный оператор
    public $operatorrecovery = 0; // Оператор клиники
    public $onexperience = 0; // На опыте
    public $operator_seeding = 0; // Оператор посева
    public $operator_bishkek = 0; // Оператор Бишкек
    public $bishkek_logist = 0; // Бишкек логист
    public $bishkek_admin_logist = 0; // Бишкек Админ логистики
    public $offline_island = 0; // Оффлайн островок
    public $logistprepayment = 0; // Логист предоплаты
    public $webmaster = 0; // Web-мастер
    public $adminsales = 0; // Админ продаж
    ////////////////////////////////////////
    public $web_access = ''; // Web-мастер доступы для роли
    public $Predictive = 0;
    public $Group = 0;
    public $team = 0;
    public $Rank = 0;
    public $staff_oper_use = 0; // Является ответственным
    public $IsResponsible = 0; // Является ответственным
    public $IsCurator = 0; // Является куратором
    public $IsPost = 0; // Почта
    public $Sip = '';
    public $Location = '';
    public $timeZoneId = 361;
    public $timeZoneName = '';
    public $Delivers = '';

    ###############################################################

    function __construct($Id = '') {
        $this->Id = $Id;
        $this->Id = (int) $this->Id;
        if (!empty($Id)) {
            $this->load();
        }
    }

    function load($Id = '') {
        if (!empty($Id)) {
            $this->Id = $Id;
        }
        $this->Id = (int) $this->Id;
        $Result = false;
        $VUQuery = "SELECT  id,
                            Password,
                            Location,
                            Level,
                            Predictive,
                            Sip,
                            FirstName,
                            LastName,
                            Position,
                            Responsible,
                            Birthday,
                            time_zone_id,
                            created_at,
                            Delivers,
                            Skype,
                            Rank,
                            Email,
                            Phone,
                            Notes,
                            Bonuses,
                            PaymentOption,
                            PaymentBase,
                            staff_oper_use,
                            IsResponsible,
                            IsCurator,
                            IsPost,
                            Ban,
                            Domain,
                            Type,
                            web_access,
                            time_zone_id,
                            time_zone_name

                    FROM Staff
                        LEFT JOIN time_zone USING(time_zone_id)
                    WHERE id = %i";

        if (($data = DB::queryOneRow($VUQuery, $this->Id))) {
            $this->Id = $data['id'];
            $this->Password = $data['Password'];
            $this->Location = $data['Location'];
            $this->Level = $data['Level'];
            $this->Delivers = $data['Delivers'];
            $this->Predictive = (int) $data['Predictive'];
            $this->Sip = $data['Sip'];
            $this->FirstName = $data['FirstName'];
            $this->LastName = $data['LastName'];
            $this->Position = $data['Position'];
            $this->Responsible = $data['Responsible'];
            $this->Birthday = $data['Birthday'];
            $this->timeZoneId = $data['time_zone_id'];
            $this->timeZoneName = empty($data['time_zone_name']) ? '' : $data['time_zone_name'];
            $this->created_at = $data['created_at'];
            $this->Skype = $data['Skype'];
            $this->Email = $data['Email'];
            $this->Phone = $data['Phone'];
            $this->Notes = $data['Notes'];
            $this->Bonuses = $data['Bonuses'];
            $this->PaymentOption = $data['PaymentOption'];
            $this->PaymentBase = $data['PaymentBase'];
            $this->Group = $data['Bonuses'];
            $this->team = $data['team'];
            $this->Rank = $data['Rank'];
            $this->staff_oper_use = (int) $data['staff_oper_use'];
            $this->IsResponsible = (int) $data['IsResponsible'];
            $this->IsCurator = (int) $data['IsCurator'];
            $this->IsPost = (int) $data['IsPost'];
            $this->Ban = $data['Ban'];
            $this->Domain = $data['Domain'];
            $this->Type = $data['Type'];
            $this->web_access = $data['web_access'];
            // Роли
            $this->admin = (int) (($this->Level & 1) > 0);
            $this->logist = (int) (($this->Level & 2) > 0);
            $this->operator = (int) (($this->Level & 4) > 0);
            $this->adminlogist = (int) (($this->Level & 8) > 0);
            $this->adminlogistpost = (int) (($this->Level & 16) > 0);
            $this->logistcity = (int) (($this->Level & 32) > 0);
            $this->admincity = (int) (($this->Level & 64) > 0);
            $this->postlogist = (int) (($this->Level & 128) > 0);
            $this->incomeoper = (int) (($this->Level & 256) > 0);
            $this->operatorcold = (int) (($this->Level & 512) > 0);
            $this->operatorrecovery = (int) (($this->Level & 1024) > 0);
            $this->whatsappoperator = (int) (($this->Level & 2048) > 0);
            $this->adminsales = (int) (($this->Level & 4096) > 0);
            $this->webmaster = (int) (($this->Level & 8192) > 0);
            $this->onexperience = (int) (($this->Level & 16384) > 0);
            $this->operator_seeding = (int) (($this->Level & 32768) > 0);
            $this->operator_bishkek = (int) (($this->Level & 65536) > 0);
            $this->bishkek_logist = (int) (($this->Level & 131072) > 0);
            $this->bishkek_admin_logist = (int) (($this->Level & 262144) > 0);
            $this->offline_island = (int) (($this->Level & 524288) > 0);
            $this->logistprepayment = (int) (($this->Level & 1048576) > 0);

            $Result = true;
        }
        $this->getLoadedState();
        return $Result;
    }

    function del($id) {
        $Result = true;
        $MQuery = "DELETE FROM Staff WHERE id = '" . $id . "'";
        $MResult = db_execute_query($MQuery);
        return $this;
    }

    function save() {
        $Result = true;
        $MQuery = "SELECT id FROM Staff WHERE id = '" . $this->Id . "'";
        $MResult = db_execute_query($MQuery);
        $MNum_Rows = mysql_num_rows($MResult);
        if ($MNum_Rows == 0) {
            $InsertQuery = "INSERT Staff
                         SET id              = '" . mysql_real_escape_string($this->Id) . "',
                             Password        = '" . mysql_real_escape_string($this->Password) . "',
                             FirstName       = '" . mysql_real_escape_string($this->FirstName) . "',
                             LastName        = '" . mysql_real_escape_string($this->LastName) . "',
                             Position        = '" . mysql_real_escape_string($this->Position) . "',
                             Responsible     = '" . mysql_real_escape_string($this->Responsible) . "',
                             Birthday        = '" . mysql_real_escape_string($this->Birthday) . "',
                             Skype           = '" . mysql_real_escape_string($this->Skype) . "',
                             Rank            = '" . mysql_real_escape_string($this->Rank) . "',
                             Email           = '" . mysql_real_escape_string($this->Email) . "',
                             Phone           = '" . mysql_real_escape_string($this->Phone) . "',
                             Notes           = '" . mysql_real_escape_string($this->Notes) . "',
                             Bonuses         = '" . mysql_real_escape_string($this->Bonuses) . "',
                             PaymentOption   = '" . mysql_real_escape_string($this->PaymentOption) . "',
                             PaymentBase     = '" . mysql_real_escape_string($this->PaymentBase) . "',
                             staff_oper_use   = '" . mysql_real_escape_string($this->staff_oper_use) . "',
                             IsResponsible   = '" . mysql_real_escape_string($this->IsResponsible) . "',
                             IsCurator       = '" . mysql_real_escape_string($this->IsCurator) . "',
                             IsPost       = '" . mysql_real_escape_string($this->IsPost) . "',
                             Ban             = '" . mysql_real_escape_string($this->Ban) . "',
                             Domain          = '" . mysql_real_escape_string($this->Domain) . "',
                             Type            = '" . mysql_real_escape_string($this->Type) . "'  ; ";
            db_execute_query($InsertQuery) or $Result = false;
        } else {
            $UpdateQuery = "UPDATE Staff
                         SET Password        = '" . mysql_real_escape_string($this->Password) . "',
                             Level           = '" . mysql_real_escape_string($this->Level) . "',
                             FirstName       = '" . mysql_real_escape_string($this->FirstName) . "',
                             LastName        = '" . mysql_real_escape_string($this->LastName) . "',
                             Position        = '" . mysql_real_escape_string($this->Position) . "',
                             Responsible     = '" . mysql_real_escape_string($this->Responsible) . "',
                             Birthday        = '" . mysql_real_escape_string($this->Birthday) . "',
                             Skype           = '" . mysql_real_escape_string($this->Skype) . "',
                             Rank            = '" . mysql_real_escape_string($this->Rank) . "',
                             Email           = '" . mysql_real_escape_string($this->Email) . "',
                             Phone           = '" . mysql_real_escape_string($this->Phone) . "',
                             Notes           = '" . mysql_real_escape_string($this->Notes) . "',
                             Bonuses         = '" . mysql_real_escape_string($this->Bonuses) . "',
                             PaymentOption   = '" . mysql_real_escape_string($this->PaymentOption) . "',
                             PaymentBase     = '" . mysql_real_escape_string($this->PaymentBase) . "',
                             staff_oper_use   = '" . mysql_real_escape_string($this->staff_oper_use) . "',
                             IsResponsible   = '" . mysql_real_escape_string($this->IsResponsible) . "',
                             IsCurator   = '" . mysql_real_escape_string($this->IsCurator) . "',
                             IsPost   = '" . mysql_real_escape_string($this->IsPost) . "',
                             Ban             = '" . mysql_real_escape_string($this->Ban) . "',
                             Domain          = '" . mysql_real_escape_string($this->Domain) . "',
                             Type            = '" . mysql_real_escape_string($this->Type) . "'
                       WHERE id              = '" . (int) $this->Id . "';";
            $UpdateQueryResult = db_execute_query($UpdateQuery) or $Result = false;
        }
        return $this;
    }

    function getLoadedState() {
        $MResult = true;
        foreach (get_class_vars(get_class($this)) as $name => $value) {
            if ($name != 'LoadedState') {
                $this->LoadedState[$name] = $this->$name;
            }
        }
        return $MResult;
    }

    function getChanges() {
        $Result = Array();
        foreach ($this->LoadedState as $name => $value) {
            if ($this->LoadedState[$name] != $this->$name) {
                if ($name == 'Password') {
                    $Result[$name] = Array('was' => '', 'set' => '');
                } else {
                    $Result[$name] = Array('was' => $this->LoadedState[$name], 'set' => $this->$name);
                }
            }
        }
        return $Result;
    }

}
