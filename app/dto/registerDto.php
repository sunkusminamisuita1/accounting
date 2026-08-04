
<?php
// app/dto/registerDto.php
class RegisterDto
{
    public string $userName;
    public string $email;
    public string $password;
    public int $fiscalMonth;
    public int $fiscalDay;

    public function __construct(
        string $userName,
        string $email,
        string $password,
        int $fiscalMonth,
        int $fiscalDay
    ) {
        $this->userName = $userName;
        $this->email = $email;
        $this->password = $password;
        $this->fiscalMonth = $fiscalMonth;
        $this->fiscalDay = $fiscalDay;
    }
}
?>