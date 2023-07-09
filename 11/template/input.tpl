<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
        <title>ProParser</title>
    </head>
    <body>
        <form action="index.php" method="POST">
              
            <div class="container w-75">

                    
                    <div class="input-group mt-4 w-100">
                        <span class="input-group-text bg-success text-white" id="basic-addon1"><b>What is search?</b></span>
                        <input name="site" type="text" class="form-control" placeholder="ozon.ru [ ozon | www.ozon.ru | https://www.ozon.ru/... ]">
                    </div>

                    <div class="input-group mt-4 w-100">
                        
                        <span class="input-group-text bg-success text-white" id="basic-addon1"><b>Keywords:</b></span>
                        <input name="req" type="text" class="form-control" placeholder="купить лыжи">        
                        
                        <div class="input-group-prepend w-25">   
                            <div class="input-group">
                                <span class="input-group-text bg-success text-white" id="basic-addon1"><b>Depth:</b></span>
                                <input  name="dep" type="number" class="form-control" placeholder="50">
                            </div>
                        </div>                
                
                    </div>
                <div class="conteiner w-25 mt-4 w-100">
                    <div class="form-check form-check-block mb-2">
                      <input class="form-check-input " type="radio" name="flexRadioDefault" id="flexRadioDefault1" checked value="google">
                      <label class="form-check-label" for="flexRadioDefault1">
                        Google
                      </label>
                    </div>

                    <div class="form-check form-check-block mb-2">
                      <input class="form-check-input " type="radio" name="flexRadioDefault" id="flexRadioDefault2" value="yandex">
                      <label class="form-check-label" for="flexRadioDefault2">
                        Yandex
                      </label>
                    </div>

                    <div class="form-check form-check-block mb-2">
                      <input class="form-check-input " type="radio" name="flexRadioDefault" id="flexRadioDefault3" value="bing">
                      <label class="form-check-label" for="flexRadioDefault3">
                        Bing
                      </label>
                    </div>

                    <div class=''>
                        <button type="submit" class="btn-lg btn-warning"><b>Find!</b></button>
                    </div>            
        </div>
                </div>
                
        </form>
    </body>
</html>