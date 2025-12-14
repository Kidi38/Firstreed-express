(function(){
  'use strict';

  function getQueryParam(name) {
    const urlParams = new URLSearchParams(window.location.search);
    return urlParams.get(name);
  }

  function formatDate(d){
    // naive yyyy-mm-dd -> dd/mm/yyyy
    if(!d) return '';
    const parts = d.split('-');
    if(parts.length===3) return parts[2] + '/' + parts[1] + '/' + parts[0];
    return d;
  }

  function renderTracking(data){
    const root = document.getElementById('result');
    if(!root) return;

    const html = [];
    html.push('<h4><center>Track Summary For: <b><span style="color:crimson;">' + escapeHtml(data.tcode) + '</span></b></center></h4>');
    html.push('<hr/>');
    html.push('<div>');
    html.push('<div style="background: #eee; padding: 5px 4px;">');

    html.push('<div class="row text-center"><br>');
    html.push('<div class="col-md-4">Tracking Number<h4><b>' + escapeHtml(data.tcode) + '</b></h4></div>');
    html.push('<div class="col-md-4">Origin/Departure<h4><b>' + escapeHtml(data.origin) + '</b></h4></div>');
    html.push('<div class="col-md-4">Destination<h4><b>' + escapeHtml(data.destination) + '</b></h4></div>');
    html.push('</div>');

    html.push('<hr style="border: 1px solid #fff;" />');

    html.push('<div class="row text-center">');
    html.push('<div class="col-md-4">Sender<h4><b>' + escapeHtml(data.sender.name) + '</b><br><small>' + escapeHtml(data.sender.phone) + '</small><br><small>' + escapeHtml(data.sender.email) + '</small></h4></div>');
    html.push('<div class="col-md-4"><br><span style="color:chocolate; font-weight:600;">Expected Delivery</span><h5><b>' + escapeHtml(formatDate(data.expectedDelivery)) + '</b></h5><br></div>');
    html.push('<div class="col-md-4">Receiver<h4><b>' + escapeHtml(data.receiver.name) + '</b><br><small>' + escapeHtml(data.receiver.phone) + '</small><br><small>' + escapeHtml(data.receiver.email) + '</small></h4></div>');
    html.push('</div>');

    html.push('<hr style="border: 1px solid #fff;" />');

    html.push('<div class="row text-center">');
    html.push('<div class="col-md-4">Item <br><h5><b>' + escapeHtml(data.item) + '</b></h5></div>');
    html.push('<div class="col-md-4">Status<h4><b><span style="color: green; text-transform:uppercase;">' + escapeHtml(data.status) + '</span></b></h4></div>');
    html.push('<div class="col-md-4">Date/Time<h5><b>' + escapeHtml(formatDate(data.date)) + '</b></h5></div>');
    html.push('</div>');

    html.push('</div>');
    html.push('<hr/>');

    html.push('<div class="text-center1"><center><h4><b>Shipment Progress</b></h4><img src="images/barcode.png" style="width: 220px;" /><br><h4><b>' + escapeHtml(data.tcode) + '</b></h4></center></div>');

    html.push('<hr><br>');

    html.push('<div class="row"><div class="col-md-9 col-md-offset-2">');
    html.push('<ul class="history">');
    if(Array.isArray(data.history)){
      data.history.forEach(function(h, idx){
        html.push('<li>');
        html.push('<h4 class="title">' + escapeHtml(h.location) + '</h4>');
        html.push('<strong>' + escapeHtml(h.date) + '</strong> at ' + escapeHtml(h.time) + '<br>');
        html.push('<p>' + escapeHtml(h.desc) + '</p>');
        html.push('</li>');
      });
    }
    html.push('</ul></div></div><br></div>');

    root.innerHTML = html.join('\n');
    root.classList.remove('hidden');
  }

  function escapeHtml(s){
    if(!s && s !== 0) return '';
    return String(s)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#039;');
  }

  function showError(message){
    const root = document.getElementById('result');
    if(!root) return;
    root.innerHTML = '<div class="alert alert-warning" style="padding:20px;">' + escapeHtml(message) + '</div>';
    root.classList.remove('hidden');
  }

  // On page load, check for tcode and fetch
  document.addEventListener('DOMContentLoaded', function(){
    const t = getQueryParam('tcode') || getQueryParam('track');
    if(!t) return;
    // fetch API (Node)
    fetch('/api/track?tcode=' + encodeURIComponent(t))
      .then(function(resp){
        if(!resp.ok) throw resp;
        return resp.json();
      })
      .then(function(data){
        renderTracking(data);
      })
      .catch(function(err){
        if(err && typeof err.json === 'function'){
          err.json().then(function(j){ showError(j.error || 'Not found'); }).catch(function(){ showError('Not found'); });
        } else {
          showError('Tracking information not found for ' + t);
        }
      });
  });

})();
