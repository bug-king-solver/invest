import{d as V,u as k,y as E,f as w,E as F,o as c,c as p,b as e,t as o,V as N,n as h,z as a,v as $,B as v,m as y,e as P,w as A,a as L,D as S,X as M}from"./main.e15b396c.js";import{_ as T}from"./Button.vue_vue_type_script_setup_true_lang.6d976816.js";import{u as B,r as b,m as D,s as Y,e as z}from"./index.esm.a56de957.js";const j={class:"panel p-8"},q={class:"mb-5"},I={class:"font-semibold text-lg mb-4"},X={class:"mb-5"},G=["onSubmit"],H={for:"current_password"},J=["placeholder"],K={key:0,class:"text-danger mt-1"},O={for:"password"},Q=["placeholder"],R={key:0,class:"text-danger mt-1"},W={key:0},Z={key:1},x={for:"password_confirmation"},ee=["placeholder"],se={key:0,class:"text-danger mt-1"},ae=V({__name:"ChangePassword",setup(C){k({title:"Profile"});const f=E(),m=w(!1),_=w(!1),l=w({current_password:"",password:"",password_confirmation:""}),g=F(()=>l.value.password),i={form:{current_password:{required:b},password:{required:b,minLength:D(8)},password_confirmation:{required:b,sameAsPassword:Y(g)}}},d=B(i,{form:l}),n=()=>{l.value={current_password:"",password:"",password_confirmation:""},d.value.form.$reset()},U=()=>{if(m.value=!0,d.value.form.$touch(),d.value.form.$invalid)return!1;_.value=!0,f.updatePassword(l.value).then(t=>{t.success&&S("Successfully Updated!"),n()}).catch(t=>{var s,r;S(((r=(s=t==null?void 0:t.response)==null?void 0:s.data)==null?void 0:r.message)||"Something went wrong. Can you please try it later.","warning")}).finally(()=>{_.value=!1})};return(t,s)=>(c(),p("div",j,[e("div",q,[e("h5",I,o(t.$t("Change Password")),1),e("p",null,o(t.$t("Changes your password")),1)]),e("div",X,[e("form",{onSubmit:N(U,["prevent"])},[e("div",{class:h([{"has-error":a(d).form.current_password.$error,"has-success":m.value&&!a(d).form.current_password.$error},"mt-5"])},[e("label",H,o(t.$t("Current password"))+" *",1),$(e("input",{id:"current_password",type:"password",placeholder:t.$t("Enter Current Password"),class:"form-input","onUpdate:modelValue":s[0]||(s[0]=r=>l.value.current_password=r)},null,8,J),[[v,l.value.current_password]]),m.value&&a(d).form.current_password.$error?(c(),p("p",K,o(t.$t("Please fill the current password")),1)):y("",!0)],2),e("div",{class:h([{"has-error":a(d).form.password.$error,"has-success":m.value&&!a(d).form.password.$error},"mt-5"])},[e("label",O,o(t.$t("New password"))+" *",1),$(e("input",{id:"password",type:"password",placeholder:t.$t("Enter New Password"),class:"form-input","onUpdate:modelValue":s[1]||(s[1]=r=>l.value.password=r)},null,8,Q),[[v,l.value.password]]),m.value&&a(d).form.password.$error?(c(),p("p",R,[a(d).form.password.minLength.$invalid?(c(),p("span",W,o(t.$t("This field should be at least 8 characters long")),1)):(c(),p("span",Z,o(t.$t("Please fill the new password")),1))])):y("",!0)],2),e("div",{class:h([{"has-error":a(d).form.password_confirmation.$error,"has-success":m.value&&!a(d).form.password_confirmation.$error},"mt-5"])},[e("label",x,o(t.$t("Password confirmation"))+" * ",1),$(e("input",{id:"password_confirmation",type:"password",placeholder:t.$t("Enter Confirm Password"),class:"form-input","onUpdate:modelValue":s[2]||(s[2]=r=>l.value.password_confirmation=r)},null,8,ee),[[v,l.value.password_confirmation]]),m.value&&a(d).form.password_confirmation.$error?(c(),p("p",se,o(t.$t("Please fill the confirm password")),1)):y("",!0)],2),P(T,{type:"submit",class:"btn btn-primary mt-5","is-loading":_.value},{default:A(()=>[L(o(t.$t("Change")),1)]),_:1},8,["is-loading"])],40,G)])]))}}),re={class:"panel p-8"},te={class:"mb-5"},oe={class:"font-semibold text-lg mb-4"},le={class:"mb-5"},ne=["onSubmit"],ie={for:"name"},de=["placeholder"],ue={key:0,class:"text-danger mt-1"},me={for:"email"},ce=["placeholder"],pe={key:0,class:"text-danger mt-1"},fe={for:"state"},he=["placeholder"],$e={for:"city"},ve=["placeholder"],_e={for:"address"},we=["placeholder"],ye=V({__name:"UpdateProfile",props:{user:null},setup(C){const{user:f}=C;k({title:"Profile"});const m=E(),{t:_}=M(),l=w(!1),g=w(!1),i=w({name:f.name,email:f.email,state:f.state,city:f.city,address:f.address}),n=B({form:{name:{required:b},email:{required:b,email:z},state:{},city:{},address:{}}},{form:i}),U=()=>{n.value.form.$reset()},t=()=>{if(l.value=!0,n.value.form.$touch(),n.value.form.$invalid)return!1;g.value=!0,m.updateProfile(i.value).then(s=>{s.success&&S(_("Your profile was successfully updated!")),m.verifyToken(),U()}).catch(s=>{var r,u;S(((u=(r=s==null?void 0:s.response)==null?void 0:r.data)==null?void 0:u.message)||_("Something went wrong. Please try it later"),"warning")}).finally(()=>{g.value=!1})};return(s,r)=>(c(),p("div",re,[e("div",te,[e("h5",oe,o(s.$t("Update Profile")),1),e("p",null,o(s.$t("Changes your personal information.")),1)]),e("div",le,[e("form",{onSubmit:N(t,["prevent"])},[e("div",{class:h([{"has-error":a(n).form.name.$error,"has-success":l.value&&!a(n).form.name.$error},"mt-5"])},[e("label",ie,o(s.$t("Name"))+" *",1),$(e("input",{id:"name",type:"text",placeholder:s.$t("Enter Your Name"),class:"form-input","onUpdate:modelValue":r[0]||(r[0]=u=>i.value.name=u)},null,8,de),[[v,i.value.name]]),l.value&&a(n).form.name.$error?(c(),p("p",ue,o(s.$t("Please fill your name")),1)):y("",!0)],2),e("div",{class:h([{"has-error":a(n).form.email.$error,"has-success":l.value&&!a(n).form.email.$error},"mt-5"])},[e("label",me,o(s.$t("Email"))+" *",1),$(e("input",{id:"email",type:"text",placeholder:s.$t("Enter Your email"),class:"form-input","onUpdate:modelValue":r[1]||(r[1]=u=>i.value.email=u)},null,8,ce),[[v,i.value.email]]),l.value&&a(n).form.email.$error?(c(),p("p",pe,o(s.$t("Please fill your email")),1)):y("",!0)],2),e("div",{class:h(["mb-5",{"has-error":a(n).form.state.$error,"has-success":l.value&&!a(n).form.state.$error}])},[e("label",fe,o(s.$t("State")),1),$(e("input",{id:"state",type:"text",placeholder:s.$t("Enter State"),class:"form-input","onUpdate:modelValue":r[2]||(r[2]=u=>i.value.state=u)},null,8,he),[[v,i.value.state]])],2),e("div",{class:h(["mb-5",{"has-error":a(n).form.city.$error,"has-success":l.value&&!a(n).form.city.$error}])},[e("label",$e,o(s.$t("City")),1),$(e("input",{id:"city",type:"text",placeholder:s.$t("Enter City"),class:"form-input","onUpdate:modelValue":r[3]||(r[3]=u=>i.value.city=u)},null,8,ve),[[v,i.value.city]])],2),e("div",{class:h(["mb-5",{"has-error":a(n).form.address.$error,"has-success":l.value&&!a(n).form.address.$error}])},[e("label",_e,o(s.$t("Address")),1),$(e("input",{id:"address",type:"text",placeholder:s.$t("Enter Address"),class:"form-input","onUpdate:modelValue":r[4]||(r[4]=u=>i.value.address=u)},null,8,we),[[v,i.value.address]])],2),P(T,{type:"submit",class:"btn btn-primary mt-5","is-loading":g.value},{default:A(()=>[L(o(s.$t("Update")),1)]),_:1},8,["is-loading"])],40,ne)])]))}}),ge=e("ul",{class:"flex space-x-2 rtl:space-x-reverse"},[e("li",null,[e("a",{href:"javascript:;",class:"text-primary hover:underline"},"Users")]),e("li",{class:"before:content-['/'] ltr:before:mr-2 rtl:before:ml-2"},[e("span",null,"Account Settings")])],-1),be={key:0,class:"pt-5"},Pe={class:"grid grid-cols-1 lg:grid-cols-2 gap-5"},Ve=V({__name:"index",setup(C){k({title:"Account Setting"});const f=E();return(m,_)=>(c(),p("div",null,[ge,a(f).user?(c(),p("div",be,[e("div",null,[e("div",Pe,[P(ye,{user:a(f).user},null,8,["user"]),P(ae)])])])):y("",!0)]))}});export{Ve as default};