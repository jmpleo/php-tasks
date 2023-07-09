window.onload = function() {
    
    var inputYear  = document.querySelector('input[name=year]');
    var inputMonth = document.querySelector('input[name=month]');
   
    document.querySelector('#send').onclick = function() {
        var parameters = 'year=' + inputYear.value + '&month=' + inputMonth.value;
        ajax(parameters);
    };
};

function ajax(parameters) {
    var request = new XMLHttpRequest();
    
    request.onreadystatechange = function() {
        if (request.readyState === 4) {
            
            var content = request.responseText;
            document.querySelector('#calendar').innerHTML = content; 
        }
    };
    
    request.open('POST', 'index.php');
    request.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    request.send(parameters);
    
}