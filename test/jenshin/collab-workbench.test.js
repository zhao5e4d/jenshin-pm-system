const {test} = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');
const root = path.resolve(__dirname, '../..');
const cellSource = fs.readFileSync(path.join(root, 'extension/custom/block/ext/js/assigntomeblock/jenshin.ui.js'), 'utf8');
const viewSource = fs.readFileSync(path.join(root, 'module/jxcollab/js/view.ui.js'), 'utf8');

test('unread title keeps original modal link and adds an accessible dot', () =>
{
    const window = {renderCell: result => result};
    vm.runInNewContext(cellSource, {window, jxCollabNewItemLabel: '新事项'});
    const link = {html: '<a href="/jxcollab-view-7.html" data-toggle="modal">Item</a>'};
    const result = window.renderJxCollabCell([link], {col: {name: 'title'}, row: {data: {isUnread: true}}});
    assert.equal(result[0], link);
    assert.equal(result.length, 2);
    assert.match(result[1].html, /title="新事项"/);
    assert.match(result[1].html, /aria-label="新事项"/);
    assert.equal(window.renderJxCollabCell([link], {col: {name: 'title'}, row: {data: {isUnread: false}}}).length, 1);
    assert.equal(window.renderJxCollabCell([link], {col: {name: 'deadline'}, row: {data: {isUnread: true}}}).length, 1);
});

test('read acknowledgement updates data in every visible workbench instance', () =>
{
    const tables = [
        {options: {data: [{id: 7, title: 'Read me', isUnread: true}, {id: 8, isUnread: true}]}},
        {options: {data: [{id: '7', isUnread: true}]}}
    ];
    for(const table of tables) table.render = ({data}) => {table.options.data = data;};
    const $ = element => typeof element === 'string'
        ? {each: callback => tables.forEach(table => callback.call(table))}
        : {zui: () => element};
    const window = {renderCell: result => result};
    vm.runInNewContext(cellSource, {window, $, jxCollabNewItemLabel: '新事项'});
    window.clearJxCollabUnread(7);
    assert.equal(tables[0].options.data[0].isUnread, false);
    assert.equal(tables[0].options.data[0].title, 'Read me');
    assert.equal(tables[0].options.data[1].isUnread, true);
    assert.equal(tables[1].options.data[0].isUnread, false);
    assert.equal(tables[0].options.data.length, 2);
});

function detailHarness({visible = true, connected = true} = {})
{
    let frame;
    let responseHandler;
    const timers = [];
    const requests = [];
    const cleared = [];
    const window = {requestAnimationFrame: callback => {frame = callback;}, setTimeout: callback => timers.push(callback), getComputedStyle: () => ({visibility: 'visible'}), clearJxCollabUnread: id => cleared.push(id)};
    const $ = () => ({length: 1, 0: {isConnected: connected, getClientRects: () => visible ? [{}] : []}, attr: () => {}});
    $.ajax = options => {requests.push(options); return {done: callback => {responseHandler = callback;}};};
    vm.runInNewContext(viewSource, {window, $});
    return {window, requests, cleared, render: () => frame(), show: () => {visible = true;}, advance: () => timers.shift()(), respond: response => responseHandler(response)};
}

test('only a successful acknowledgement for the opened item clears its dot', () =>
{
    const h = detailHarness();
    h.window.markJxCollabRead({}, 7, '/jxcollab-view-7.html');
    assert.equal(h.requests.length, 0);
    h.render();
    assert.equal(h.requests[0].type, 'POST');
    assert.equal(h.requests[0].data.action, 'markRead');
    h.respond({result: 'fail'});
    h.respond({result: 'success', collabID: 8});
    assert.deepEqual(h.cleared, []);
    h.respond({result: 'success', collabID: '7'});
    assert.deepEqual(h.cleared, [7]);
});

test('hidden or unmounted detail never sends a read request', () =>
{
    for(const options of [{visible: false}, {connected: false}])
    {
        const h = detailHarness(options);
        h.window.markJxCollabRead({}, 7, '/jxcollab-view-7.html');
        h.render();
        assert.equal(h.requests.length, 0);
        assert.deepEqual(h.cleared, []);
    }
});

test('a failed network request leaves the item unread', () =>
{
    const h = detailHarness();
    h.window.markJxCollabRead({}, 7, '/jxcollab-view-7.html');
    h.render();
    assert.equal(h.requests.length, 1);
    assert.deepEqual(h.cleared, []);
});

test('a modal that becomes visible after rendering is then marked read', () =>
{
    const h = detailHarness({visible: false});
    h.window.markJxCollabRead({}, 7, '/jxcollab-view-7.html');
    h.render();
    assert.equal(h.requests.length, 0);
    h.show();
    h.advance();
    assert.equal(h.requests.length, 1);
    h.respond({result: 'success', collabID: 7});
    assert.deepEqual(h.cleared, [7]);
});
