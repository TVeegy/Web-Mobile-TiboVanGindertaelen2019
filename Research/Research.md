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
    Result: 2x "TEST TEST TEST"

