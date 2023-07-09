<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
        <title>ProParser</title>
    </head>
    <body>
        <div class="container">
            <table class="table table-striped">
            <thead class="table table-dark">
                <tr class="">
                    <td colspan="2"><b>Сайт:</b><i> <!-- .$site --> </i></td>                           
                </tr>
                <tr class="">
                    <td colspan="2"><b>В выдаче поисковиком:</b><i> <!-- .$search --> </i></td>                              
                </tr>
                <tr class="">
                    <td colspan="2"><b>по запросу:</b><i> <!-- .$request --> </i></td>                          
                </tr>
            </thead>
            <tbody class="table table-light ">
                <!-- info_cycle  -->
                        <tr>
                            <th scope="col"> <!-- .$head --> </th>
                            <td>
                                <!-- .$info -->
                            </td>
                        </tr>
                <!-- info_cycle_ends -->
            </tbody>
            </table> 
        </div>
    </body>
</html>