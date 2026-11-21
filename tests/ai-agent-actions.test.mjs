import test from 'node:test';
import assert from 'node:assert/strict';
import { canExecuteField, executableFormSchema, fillExecutableForm, AgentWorkspace, hasExplicitDeleteInstruction } from '../resources/js/ai-agent-actions.js';

function field(name, value = '', extras = {}) {
 return { name, value, type:'text', tagName:'INPUT', events:[], getClientRects:()=>[1], getAttribute:()=>null, closest:()=>null, dispatchEvent(event){this.events.push(event.type)}, ...extras };
}

test('execution blocks secrets and permits verified relation choices only',()=>{
 assert.equal(canExecuteField(field('password','secret',{type:'password'})),false);
 assert.equal(canExecuteField(field('role','super_admin')),false);
 assert.equal(canExecuteField(field('school_id','99')),false);
 assert.equal(canExecuteField(field('teacher_id','99')),false);
 assert.equal(canExecuteField(field('teacher_id','1',{tagName:'SELECT'})),true);
});

test('checkbox and multi-select preserve their actual submitted values',()=>{
 const active=field('is_active','1',{type:'checkbox',checked:false});
 const audience=field('targets[]','A',{type:'checkbox',checked:false});
 const audience2=field('targets[]','B',{type:'checkbox',checked:false});
 const form={elements:[active,audience,audience2]};
 fillExecutableForm(form,[{name:'is_active',value:true},{name:'targets[]',value:['B']}]);
 assert.equal(active.checked,true);assert.equal(audience.checked,false);assert.equal(audience2.checked,true);
 assert.deepEqual(executableFormSchema(form).find(item=>item.name==='targets[]').value,['B']);
});

test('execution rejects unknown fields and fabricated dropdown IDs',()=>{
 const teacher=field('teacher_id','1',{tagName:'SELECT',options:[{value:'1',textContent:'Guru A'}]});
 assert.throws(()=>fillExecutableForm({elements:[teacher]},[{name:'teacher_id',value:'999'}]),/tidak tersedia/);
 assert.throws(()=>fillExecutableForm({elements:[teacher]},[{name:'school_id',value:'2'}]),/tidak tersedia/);
 assert.equal(teacher.value,'1');
});

test('save and delete cannot execute without a verified form and target',async()=>{
 const workspace=new AgentWorkspace();
 await assert.rejects(workspace.saveForm({}, {resource:'subjects',fields:[{name:'name',value:'New'}]}),/Buka formulir/);
 await assert.rejects(workspace.deleteRecord({}, {resource:'subjects',record_id:9},'Hapus mapel'),/pastikan target/);
 await assert.rejects(workspace.deleteRecord({}, {resource:'subjects',record_id:9},'Cari mapel'),/eksplisit/);
});

test('uncertain previous write stops subsequent mutations',async()=>{
 const workspace=new AgentWorkspace();workspace.uncertain=true;
 await assert.rejects(workspace.deleteRecord({}, {resource:'subjects',record_id:9},'Hapus mapel'),/belum terkonfirmasi/);
});

test('negated delete instruction cannot authorize deletion',()=>{
 assert.equal(hasExplicitDeleteInstruction('Hapus mata pelajaran Robotika dengan ID 9'),true);
 for(const instruction of ['Jangan hapus data', 'Cari Matematika. Jangan tambah, edit, atau hapus.', 'Tidak perlu hapus mapel', 'Do not delete anything', 'Cari data yang dihapus']) assert.equal(hasExplicitDeleteInstruction(instruction),false);
});

test('server error during a write stops subsequent mutations as uncertain',async()=>{
 const originalFetch=globalThis.fetch, originalWindow=globalThis.window, originalDocument=globalThis.document;
 globalThis.window={location:{origin:'https://school.test'}};
 globalThis.document={querySelector:()=>({content:'csrf-test'})};
 globalThis.fetch=async()=>({ok:false,status:500,json:async()=>({message:'Server error'}),headers:{get:()=> 'application/json'}});
 try {
  const workspace=new AgentWorkspace();workspace.knownRecords.set('subjects',new Set([9]));
  const result=await workspace.deleteRecord({routes:{destroy:'/subjects/__ID__'}},{resource:'subjects',record_id:9},'Hapus mapel ID 9');
  assert.equal(result.uncertain,true);assert.equal(workspace.uncertain,true);
  await assert.rejects(workspace.deleteRecord({}, {resource:'subjects',record_id:9},'Hapus mapel ID 9'),/belum terkonfirmasi/);
 } finally {globalThis.fetch=originalFetch;globalThis.window=originalWindow;globalThis.document=originalDocument;}
});
import { aiAgentChat } from '../resources/js/ai-agent-actions.js';

test('Enter sends chat, Shift Enter and IME composition keep editing',async()=>{
 const chat=aiAgentChat('/run','/status');let sends=0,prevented=0;chat.send=async()=>{sends++};
 const event=(extra={})=>({key:'Enter',shiftKey:false,isComposing:false,preventDefault(){prevented++},...extra});
 await chat.handleComposerKeydown(event());
 await chat.handleComposerKeydown(event({shiftKey:true}));
 await chat.handleComposerKeydown(event({isComposing:true}));
 await chat.handleComposerKeydown(event({keyCode:229}));
 assert.equal(sends,1);assert.equal(prevented,1);
});

test('failed status check can recover and send same typed message with school context',async()=>{
 const originalFetch=globalThis.fetch, originalWindow=globalThis.window, originalDocument=globalThis.document;
 globalThis.window={location:{pathname:'/dashboard'}};globalThis.document={querySelector:()=>({content:'csrf-test'})};
 let checks=0;const requests=[];
 globalThis.fetch=async(url,options)=>{
  if(url==='/status'){checks++;return checks===1?{ok:false,status:403,json:async()=>({message:'Pilih sekolah.'})}:{ok:true,status:200,json:async()=>({enabled:true,configured:true,school_id:7})};}
  requests.push(options);return {ok:true,status:200,json:async()=>({reply:'Halo, ada yang bisa saya bantu?',tool:null})};
 };
 try {
  const chat=aiAgentChat('/run','/status');chat.$nextTick=()=>{};chat.prompt='hi';
  assert.equal(await chat.checkStatus(),false);assert.equal(chat.error,'Pilih sekolah.');assert.equal(chat.prompt,'hi');
  await chat.send();assert.equal(requests.length,1);assert.equal(requests[0].headers['X-School-ID'],'7');assert.equal(chat.messages.at(-1).text,'Halo, ada yang bisa saya bantu?');assert.equal(chat.error,'');
 }finally{globalThis.fetch=originalFetch;globalThis.window=originalWindow;globalThis.document=originalDocument;}
});
