var isIEStatic = null;
var isIE = function () {
  if (isIEStatic === null) {
    var div = document.createElement('div');
    div.style.display = 'none';
    div.innerHTML = '<!--[if IE]><div id="ie-find"></div><[endif]-->';
    document.body.appendChild(div);

    if (document.getElementById('ie-find')) {
      isIEStatic = true;
    }
    else {
      isIEStatic = false;
    }

    document.body.removeChild(div);
  }

  return isIEStatic;
};

var isIEVersionStatics = {};
var isIEVersion = function (version) {
  if (isIEVersionStatics[version] === undefined) {
    var div = document.createElement('div');
    div.style.display = 'none';
    div.innerHTML = '<!--[if IE ' + version + ']><div id="ie-find"></div><[endif]-->';
    document.body.appendChild(div);

    if (document.getElementById('ie-find')) {
      isIEVersionStatics[version] = true;
    }
    else {
      isIEVersionStatics[version] = false;
    }

    document.body.removeChild(div);
  }

  return isIEVersionStatics[version];
};
