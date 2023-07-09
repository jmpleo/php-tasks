<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
           <title>Cars</title>
            <link rel="stylesheet" href="style.css">
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-+0n0xVW2eSR5OomGNYDnhzAbDsOXxcvSN1TPprVMTNDbiYZCxYbOOl7+AMvyTG2x" crossorigin="anonymous">
    </head>
    <body>
        <div id="my_container">
            <table class="table table-bordered">
                <tr id="head">
                    <td>manufactor</td><td>model</td><td>hp</td>
                </tr>
                <!-- hp_cycle -->
                <tr>
                    <td colspan="3" class="hp"><!-- .$hp --></td>
                </tr>
                <!-- manufactor_cycle -->
                    <tr>
                        <td rowspan="<!-- .$rowspan -->"><!-- .$manufactor --></td>
                        <!-- cars_cycle[1] -->
                        <td><!-- .$model --></td>
                        <td><!-- .$hp --></td>
                        <!-- cars_cycle_ends -->
                    </tr>
                        <!-- cars_cycle -->
                        <tr>
                            <td><!-- .$model --></td>
                            <td><!-- .$hp --></td>
                        </tr>
                        <!-- cars_cycle_ends -->
                    <!-- manufactor_cycle_ends -->
                <!-- hp_cycle_ends -->
            </table>    
        </div>
    </body>
</html>