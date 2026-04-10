
<?php
// app/DTO/RegisterDTO.php
class RegisterDTO
{
    public string $username;
    public string $email;
    public string $password;
    public int $fiscalMonth;
    public int $fiscalDay;

    public function __construct(
        string $username,
        string $email,
        string $password,
        int $fiscalMonth,
        int $fiscalDay
    ) {
        $this->username = $username;
        $this->email = $email;
        $this->password = $password;
        $this->fiscalMonth = $fiscalMonth;
        $this->fiscalDay = $fiscalDay;
    }
}
?>