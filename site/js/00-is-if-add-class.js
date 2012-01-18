if (isIE()) {
  var body = document.getElementsByTagName('body')[0];  
  if (body) {
    body.className = body.className + ' ie';
    
    var versions = [6, 7, 8, 9, 10];
    
    for (var i = 0; i < versions.length; i++) {
      if (isIEVersion(versions[i])) {
        body.className = body.className + ' ie-' + versions[i];
        break;
      }
    }
  }
}
