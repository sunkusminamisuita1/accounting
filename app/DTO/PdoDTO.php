<?php
class PdoDTO{
    public string $InstncPdo;
    
    public function __construct($Pdo){
            $this->InstncPdo = $Pdo;
    }
}
?>
