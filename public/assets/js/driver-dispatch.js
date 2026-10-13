(() => {
    const script=document.currentScript, message=document.getElementById('dispatch-message');
    const esc=value=>String(value ?? '').replace(/[&<>"']/g,char=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[char]));
    let state, busy=false;
    let historyExpanded=false;
    const voicePlaying=()=>Array.from(document.querySelectorAll?.('audio[data-sos-voice]') || []).some(audio=>!audio.paused && !audio.ended);
    function tripCard(trip) {
        const actions=trip.status==='Searching' ? ['accept','decline'] : ({Accepted:['arrive'], 'At pickup':['pickup'], 'Picked up':['first_aid'], 'First aid':['complete'], Transporting:['handoff']}[trip.status] || []);
        const photo=trip.reporter_photo_url ? `<a class="dispatch-sender-photo" href="${esc(trip.reporter_photo_url)}" target="_blank" rel="noopener"><img src="${esc(trip.reporter_photo_url)}" alt="SOS sender photo" loading="lazy"><span>SOS sender photo</span></a>` : '';
        const voice=trip.reporter_voice_url ? `<div class="dispatch-sender-voice"><span>SOS voice · 10 seconds</span><audio data-sos-voice controls preload="none" aria-label="SOS sender voice recording" src="${esc(trip.reporter_voice_url)}"></audio></div>` : '';
        return `<div class="dispatch-trip border rounded p-3 mb-3">${photo}${voice}<strong>${esc(trip.patient.name)}</strong><span class="badge bg-primary ms-2">${esc(trip.status)}</span><p>${trip.distance_km ?? '—'} km · ${trip.eta_minutes ?? '—'} min estimated</p><p>${esc(trip.patient.mobile || '')}</p>${actions.map(name=>`<button class="btn btn-sm btn-outline-primary me-2" data-action="${name}" data-id="${trip.id}">${({accept:'Accept pickup',decline:'Decline',arrive:'Reached pickup',pickup:'Patient picked up',first_aid:'Record first aid',complete:'Complete without transport',handoff:'Complete hospital handoff'})[name]}</button>`).join('')}${['Picked up','First aid','Transporting'].includes(trip.status)?`<div class="mt-3"><select class="form-select mb-2" id="care-${trip.id}">${state.care_types.map(care=>`<option>${esc(care)}</option>`).join('')}</select><select class="form-select mb-2" id="hospital-${trip.id}">${(trip.hospital_options || []).filter(h=>h.status==='Open').map(h=>`<option value="${h.id}">${esc(h.name)}</option>`).join('')}</select><button class="btn btn-sm btn-primary" data-action="transport" data-id="${trip.id}">Request hospital transfer</button></div>`:''}</div>`;
    }
    function renderTripHistory() {
        const region=document.getElementById('driver-trip-history');
        if(!region)return;
        const terminal=['Completed','Cancelled','Expired'];
        const history=state.calls.filter(trip=>terminal.includes(trip.status)).sort((a,b)=>b.created_at-a.created_at);
        const visibleHistory=historyExpanded?history:history.slice(0,2);
        region.hidden=!history.length;
        region.innerHTML=history.length?`<div class="dispatch-section-heading"><h4>Recent trips</h4>${history.length>2?`<button type="button" class="history-toggle" data-history-toggle aria-controls="driver-history-records" aria-expanded="${historyExpanded}">${historyExpanded?'View less':'View all'}</button>`:''}</div><div id="driver-history-records">${visibleHistory.map(tripCard).join('')}</div>`:'';
    }
    function renderTrips() {
        // Keep the current audio element alive while the ten-second clip plays.
        if(voicePlaying())return;
        const terminal=['Completed','Cancelled','Expired'];
        const active=state.calls.filter(trip=>!terminal.includes(trip.status));
        const activeMarkup=active.length?`<div class="dispatch-section-heading"><h4>Active trips</h4></div>${active.map(tripCard).join('')}`:'<p class="text-muted">No pickup offers right now.</p>';
        document.getElementById('driver-trips').innerHTML=activeMarkup+'<section class="dispatch-history" id="driver-trip-history"></section>';
        renderTripHistory();
    }
    async function action(payload) {
        if (busy) return;
        busy=true;
        try {
            const response=await fetch(script.dataset.actionUrl,{method:'POST',headers:{'Content-Type':'application/json',Accept:'application/json','X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content},body:JSON.stringify(payload)});
            const result=await response.json(); if(!response.ok)throw Error(result.message || Object.values(result.errors || {})[0]?.[0] || 'Unable to save');
            message.textContent=result.message; await refresh();
        } catch(error) { message.textContent=error.message; } finally { busy=false; }
    }
    async function refresh() {
        try {
            const response=await fetch(script.dataset.stateUrl,{headers:{Accept:'application/json'},cache:'no-store'});
            if(!response.ok)throw Error('Dispatch unavailable. Check your login and connection.');
            state=await response.json(); const driver=state.drivers[0];
            if(message.textContent==='Loading dispatch…') message.textContent='Dispatch ready. Go online to receive pickup offers.';
            document.getElementById('driver-details').textContent=driver.name+' · '+driver.vehicle+' · '+driver.phone;
            document.getElementById('driver-duty').textContent=driver.on_duty ? 'Go offline' : 'Go online with GPS';
            renderTrips();
        } catch(error) { message.textContent=error.message; }
    }
    document.getElementById('driver-duty').addEventListener('click',()=>{
        if(state?.drivers[0]?.on_duty) return action({action:'duty',on_duty:false});
        if(!navigator.geolocation) {message.textContent='GPS is unavailable in this browser.';return;}
        navigator.geolocation.getCurrentPosition(position=>action({action:'duty',on_duty:true,lat:position.coords.latitude,lng:position.coords.longitude,mode:'gps'}),()=>{message.textContent='Enable location access, or use the Android app for GPS on your local network.';},{enableHighAccuracy:true,timeout:15000});
    });
    document.getElementById('driver-trips').addEventListener('click',event=>{
        if(!event.target.closest('[data-history-toggle]'))return;
        if(voicePlaying())return;
        historyExpanded=!historyExpanded;renderTripHistory();
    });
    for(const name of ['ended','pause'])document.getElementById('driver-trips').addEventListener(name,event=>{
        if(event.target.matches?.('audio[data-sos-voice]') && !voicePlaying())renderTrips();
    },true);
    document.getElementById('driver-trips').addEventListener('click',event=>{
        const button=event.target.closest('[data-action]'); if(!button)return;
        const payload={action:button.dataset.action,id:button.dataset.id};
        if(payload.action==='transport') {payload.care=document.getElementById('care-'+payload.id).value;payload.hospital_id=document.getElementById('hospital-'+payload.id).value;}
        action(payload);
    });
    refresh(); setInterval(()=>{if(!busy && !document.hidden && document.activeElement?.tagName!=='SELECT')refresh();},5000);
})();
