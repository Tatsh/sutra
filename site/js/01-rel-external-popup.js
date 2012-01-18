var hrefs = document.getElementsByTagName('a');
for (var i = 0; i < hrefs.length; i++) {
  if (hrefs[i].getAttribute('rel') === 'external') {
    hrefs[i].setAttribute('target', '_blank');
  }
}
