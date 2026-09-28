
<?php
// app/dto/registerDto.php
class authDto
{
    public string $userName;
    public string $email;
    public string $password;
    public int    $fiscalMonth;
    public int    $fiscalDay;
    public array  $user;          //loginDto
    public array  $userShops;     //loginDto


    public function __construct(
        string $userName,
        string $email,
        string $password,
        int $fiscalMonth,
        int $fiscalDay
    ) {
        $this->userName     = $userName;
        $this->email        = $email;
        $this->password     = $password;
        $this->fiscalMonth  = $fiscalMonth;
        $this->fiscalDay    = $fiscalDay;
        $this->user         =   [];    //loginDto
        $this->userShops    =   [];    //loginDto 
    }
}
?>