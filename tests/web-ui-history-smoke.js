const assert = require('node:assert/strict');
const fs = require('node:fs');
const vm = require('node:vm');
const path = require('node:path');
const {execFileSync} = require('node:child_process');

function element(text = '') {
    let markup = '';
    return {
        textContent: text, hidden: false, listeners: {}, attributes: {}, htmlWrites: 0,
        get innerHTML() { return markup; },
        set innerHTML(value) { markup = value; this.htmlWrites++; },
        setAttribute(name, value) { this.attributes[name] = value; },
        addEventListener(name, callback) { (this.listeners[name] ||= []).push(callback); },
        classList: { add() {}, remove() {} },
    };
}

async function settle() { await new Promise(resolve => setImmediate(resolve)); }

async function checkEvents() {
    const phpBinary = process.env.PHP_BINARY || 'C:/xampp/php/php.exe';
    const source = execFileSync(phpBinary, [path.join(__dirname, 'support/render-emergency-ui.php')], {encoding: 'utf8'});
    const nodes = {events: element(), 'history-toggle': element(), connection: element(), toast: element()};
    const fixture = {
        profile: {role: 'patient'}, requests: [], stats: {}, csrf_token: 'test-only',
        events: Object.freeze([1, 2, 3, 4, 5].map(at => ({at, message: `Activity ${at}`}))),
    };
    let reads = 0;
    const context = {
        document: {
            hidden: false, activeElement: null,
            getElementById: id => nodes[id] || null,
            querySelector: () => ({content: 'test-only'}), querySelectorAll: () => [],
            addEventListener() {},
        },
        location: {search: '', hash: ''}, window: {addEventListener() {}}, URLSearchParams,
        fetch: async () => {reads++; return {ok: true, json: async () => fixture};},
        setInterval() {}, setTimeout() {}, clearTimeout() {}, console,
    };
    vm.runInNewContext(source, context);
    await settle();
    assert.equal((nodes.events.innerHTML.match(/class="event"/g) || []).length, 2);
    assert.match(nodes.events.innerHTML, /Activity 5/);
    assert.match(nodes.events.innerHTML, /Activity 4/);
    assert.doesNotMatch(nodes.events.innerHTML, /Activity 1/);
    assert.equal(nodes['history-toggle'].attributes['aria-expanded'], 'false');
    nodes['history-toggle'].listeners.click[0]();
    assert.equal((nodes.events.innerHTML.match(/class="event"/g) || []).length, 5);
    assert.equal(nodes['history-toggle'].attributes['aria-expanded'], 'true');
    nodes['history-toggle'].listeners.click[0]();
    assert.equal((nodes.events.innerHTML.match(/class="event"/g) || []).length, 2);
    assert.equal(fixture.events.length, 5);
    assert.equal(reads, 1, 'View all / less must not make a network request');
}

async function checkTrips() {
    const source = fs.readFileSync(path.join(__dirname, '../public/assets/js/driver-dispatch.js'), 'utf8');
    const nodes = {
        'dispatch-message': element('Loading dispatch…'), 'driver-details': element(), 'driver-duty': element(),
        'driver-trips': element(), 'driver-trip-history': element(),
        'care-Active four': {value: 'General'}, 'hospital-Active four': {value: 'h1'},
    };
    const call = (id, status, created_at, extra = {}) => Object.freeze({id, status, created_at, patient: {name: id, mobile: 'Test contact'}, distance_km: 2, eta_minutes: 5, ...extra});
    const fixture = {
        drivers: [{name: 'Test driver', vehicle: 'TEST', phone: 'Test contact', on_duty: false}],
        calls: Object.freeze([
            call('Active one', 'Searching', 10, {reporter_photo_url: '/emergency/dispatch/one/reporter-photo?test="&', reporter_voice_url: '/emergency/dispatch/one/reporter-voice?test="&'}), call('Active two', 'Accepted', 9), call('Active three', 'At pickup', 8),
            call('Active four', 'Picked up', 7, {hospital_options: [{id: 'h1', name: 'Hospital one', status: 'Open'}, {id: 'h2', name: 'Hospital two', status: 'Open'}]}),
            call('Old one', 'Completed', 1), call('Old two', 'Cancelled', 2), call('Old three', 'Expired', 3),
        ]), care_types: ['General', 'ICU'],
    };
    let reads = 0, tick;
    const audio={paused:true, ended:false};
    const context = {
        document: {
            currentScript: {dataset: {stateUrl: 'test-state', actionUrl: 'test-action'}}, hidden: false, activeElement: null,
            getElementById: id => nodes[id] || null, querySelector: () => ({content: 'test-only'}),
            querySelectorAll: () => [audio],
        },
        navigator: {}, fetch: async () => {reads++; return {ok: true, json: async () => fixture};}, setInterval(callback) {tick=callback;}, console,
    };
    vm.runInNewContext(source, context);
    await settle();
    for (const name of ['Active one', 'Active two', 'Active three', 'Active four']) assert.match(nodes['driver-trips'].innerHTML, new RegExp(name));
    for (const name of ['Old two', 'Old three']) assert.match(nodes['driver-trip-history'].innerHTML, new RegExp(name));
    assert.doesNotMatch(nodes['driver-trip-history'].innerHTML, /Old one/);
    assert.match(nodes['driver-trips'].innerHTML, /data-action="accept"/);
    assert.match(nodes['driver-trips'].innerHTML, /alt="SOS sender photo"/);
    assert.match(nodes['driver-trips'].innerHTML, /src="\/emergency\/dispatch\/one\/reporter-photo\?test=&quot;&amp;"/);
    assert.equal((nodes['driver-trips'].innerHTML.match(/alt="SOS sender photo"/g) || []).length, 1, 'Legacy trips do not show a broken photo');
    assert.match(nodes['driver-trips'].innerHTML, /audio data-sos-voice controls preload="none"/);
    assert.match(nodes['driver-trips'].innerHTML, /reporter-voice\?test=&quot;&amp;/);
    assert.equal((nodes['driver-trips'].innerHTML.match(/audio data-sos-voice/g) || []).length, 1, 'Legacy trips do not show a broken audio control');
    assert.match(nodes['driver-trips'].innerHTML, /data-action="arrive"/);
    assert.match(nodes['driver-trips'].innerHTML, /data-action="pickup"/);
    assert.match(nodes['driver-trips'].innerHTML, /data-action="transport"/);
    // Simulate choosing a care type and destination for an active pickup.
    nodes['care-Active four'].value = 'ICU';
    nodes['hospital-Active four'].value = 'h2';
    const activeWrites = nodes['driver-trips'].htmlWrites;
    const activeMarkup = nodes['driver-trips'].innerHTML;
    const toggleEvent = {target: {closest: selector => selector === '[data-history-toggle]' ? {} : null}};
    nodes['driver-trips'].listeners.click[0](toggleEvent);
    assert.match(nodes['driver-trip-history'].innerHTML, /Old one/);
    assert.match(nodes['driver-trip-history'].innerHTML, /View less/);
    nodes['driver-trips'].listeners.click[0](toggleEvent);
    assert.doesNotMatch(nodes['driver-trip-history'].innerHTML, /Old one/);
    assert.match(nodes['driver-trip-history'].innerHTML, /View all/);
    assert.equal(nodes['driver-trips'].htmlWrites, activeWrites, 'History toggle must not replace the active trip container');
    assert.equal(nodes['driver-trips'].innerHTML, activeMarkup);
    assert.equal(nodes['care-Active four'].value, 'ICU', 'History toggle preserves selected care');
    assert.equal(nodes['hospital-Active four'].value, 'h2', 'History toggle preserves selected hospital');
    assert.equal(fixture.calls.length, 7);
    assert.equal(reads, 1);
    audio.paused=false;
    const beforePlayback=nodes['driver-trips'].htmlWrites;
    tick();
    await settle();
    assert.equal(reads, 2, 'Dispatch keeps fetching state while voice plays');
    assert.equal(nodes['driver-trips'].htmlWrites, beforePlayback, 'Refreshing state must not cut off the voice recording');
    nodes['driver-trips'].listeners.click[0](toggleEvent);
    assert.equal(nodes['driver-trips'].htmlWrites, beforePlayback);
    audio.paused=true;
    nodes['driver-trips'].listeners.pause[0]({target:{matches:()=>true}});
    assert.equal(nodes['driver-trips'].htmlWrites, beforePlayback+1, 'The latest dispatch state renders once playback stops');
}

(async () => {
    await checkEvents();
    await checkTrips();
    console.log('PASS: Web history shows two recent records, preserves all active trips and keeps toggles local.');
})().catch(error => { console.error(error); process.exitCode = 1; });
