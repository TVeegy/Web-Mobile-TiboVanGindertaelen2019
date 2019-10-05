* Print Array:  
  * Link: https://stackoverflow.com/questions/9816889/how-to-echo-or-print-an-array-in-php  
  * Code: 
  ```php 
  echo '<pre>'; print_r($array); echo '</pre>';
  ```

* Debug in PHP:  
  * Link: https://www.php.net/manual/en/debugger.php  
  * Code: zie phpDebuggingExample.php  
  
* Passing by reference in PHP:  
  * Link: https://www.php.net/manual/en/language.references.pass.php  
  * Code (img):  
  ![Image of Passing By Reference PHP Code](https://github.com/TVeegy/Web-Mobile-TiboVanGindertaelen2019/blob/master/Research/img/PHP_PassingByReference.PNG)
  
* Calling stored functions (reference):
  * ```php
    function HandleConnectionSuccess($data = null){
    function IsNull(){
        echo "\n TEST TEST TEST \n";
    }
    $data = $data ?? IsNull;
    $data();
    $data ?? IsNull();
    $data();
    }
    ```  
  * Result: 2x "TEST TEST TEST"  

* Declareren van Methoden in Methoden:  
  * ```php
    if (!function_exists('IsNull') && !function_exists('IsNotNull')){
        function IsNull(){
            
        }
        function IsNotNull(){
            
        }
    }
    ``` 
  * Link: //https://stackoverflow.com/questions/1953857/fatal-error-cannot-redeclare-function  
* Opslaan/Callen van Methoden en Coalesce/ternaire operatoren:
  * ```php
    ($execute = ($responseData==null)?'IsNull':'IsNotNull')($responseData);
    ```  
  * link: https://www.php.net/manual/en/functions.variable-functions.php  
  * link: https://www.designcise.com/web/tutorial/whats-the-difference-between-null-coalescing-operator-and-ternary-operator-in-php  
    
