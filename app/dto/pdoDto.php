<?php
class PdoDto{
    public string $instncPdo;
    
    public function __construct($Pdo){
            $this->instncPdo = $Pdo;
    }
}
?>
