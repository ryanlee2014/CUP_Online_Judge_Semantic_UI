<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=1200">
    <meta name="description" content="">
    <meta name="author" content="">
    <link rel="icon" href="../../favicon.ico">
    <title><?php echo $OJ_NAME ?></title>
    <?php include("template/$OJ_TEMPLATE/css.php"); ?>
    <?php include("template/$OJ_TEMPLATE/extra_css.php") ?>
<link href="/css/github-markdown.min.css" rel="stylesheet" type="text/css">
    <link href="/js/styles/github.min.css" rel="stylesheet" type="text/css">
    <?php include("template/$OJ_TEMPLATE/js.php"); ?>
    <script src="/js/mavon-editor.js"></script>
    <script src="/js/markdown-it.js"></script>
    <script src="/template/semantic-ui/js/clipboard.min.js"></script>
    <script>
     Vue.use(window["mavon-editor"]);
    </script>
    <script src="/js/mermaid.min.js"></script>
    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
    <script src="http://cdn.bootcss.com/html5shiv/3.7.0/html5shiv.js"></script>
    <script src="http://cdn.bootcss.com/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->
    <style>
        .full.segment{
            height:100%;
        }
        .black{
            color: black;
        }
    </style>
</head>
<script type="text/x-template" id="discussMainContent">
    <div class="ui grid">
        <div class="four wide column">
        <div class="ui link card">
<div class="image">
<img v-if="thread_head.avatar === 1" :src="'../avatar/'+thread_head.user_id+'.jpg'" @click="location.href='userinfo.php?user='+thread_head.user_id">
<img v-else src="../assets/images/wireframe/white-image.png">
</div>
<div class="content">
<div class="header"><a class="black" :href="'userinfo.php?user='+thread_head.user_id" target="_blank">{{thread_head.nick}}</a></div>
<div class="meta">
<a :href="'userinfo.php?user='+thread_head.user_id" target="_blank">{{thread_head.user_id}}</a>
</div>
<div class="description" v-html="markdownIt.renderRaw(thread_head.biography||'')">
</div>
</div>
<div class="extra content">
<span class="right floated">

</span>
<span>
<i class="user icon"></i>
Solved {{thread_head.solved}}
</span>
</div>
</div>
<div class="ui sticky" style="left: 50.1429px;" id="sticky_content">
    <h3 class="ui header" id="contents" v-show="content">目录</h3>
    <div id="contentContainer"></div>
  </div>
      </div>
      <div class="twelve wide column">
         <div class="ui existing full segment" id="main_context">
             <a v-if="thread_head.user_id + '' === owner" class="ui blue right ribbon label" :href="'discussedit.php?id='+id">Edit</a>
             <div class="ui info message">
                 <div class="header">
                     阅读本文需要大约{{readTime(thread_head.content)}}分钟
                 </div>
             </div>
             <div v-html="thread_head.content"></div>
         </div>
      </div>
</div>
</script>
<body>
<?php include("template/$OJ_TEMPLATE/nav.php") ?>
    <!-- Main component for a primary marketing message or call to action -->
<div class="ui container padding" v-cloak>
        <h2 class="ui dividing header">Discuss</h2>
        <div class="ui breadcrumb">
        <a class="section" href="/discuss.php">讨论主页</a>
        <i class="right angle icon divider"></i>
        <div class="active section">Discuss ID:{{id}}</div>
        </div>
        <h1>{{thread_head.title}}</h1>
        <discuss-main-content :thread_head="thread_head" :content="content" :owner="owner" :id="id"></discuss-main-content>
      <h3 class="ui dividing header">Comments</h3>
      <div class="ui comments">
      <div class="comment" v-for="row in reply">
    <a class="avatar" :href="'userinfo.php?user='+row.user_id">
      <img v-if="row.avatar === 1" :src="'../avatar/'+row.user_id+'.jpg'">
      <img v-else src="../assets/images/wireframe/white-image.png">
    </a>
    <div class="content">
      <a class="author" :href="'userinfo.php?user='+row.user_id">{{row.nick}}</a>
      <div class="metadata">
        <span class="date">{{new Date(row.create_time).toLocaleString()}}</span>
      </div>
      <div class="text" v-html="row.content">
      </div>
      <div class="actions">
          <a v-if="row.user_id + '' === owner" class="reply" :href="'discussedit.php?id='+id+'&comment_id='+row.comment_id">Edit</a>
          <a v-if="isadmin" class="reply" @click="block_reply(row.comment_id)">屏蔽</a>
        <!--<a class="reply">Reply</a>-->
      </div>
    </div>
    
  </div>
  </div>
        <h3 class="ui dividing header">Reply</h3>
  <form class="ui reply form">
    <div class="field">
        <mavon-editor v-model="replyText"></mavon-editor>
    </div>
    <div class="two field">
    <div class="ui left input" style="width:auto">
                        <input v-model="captcha" name="vcode" type="text" placeholder="验证码" id="vcode" ><img alt="click to change" id="vcode_graph" src="/api/captcha?from=discuss" onclick="this.src='/api/captcha?from=discuss&random='+Math.random()" height="40px">
                    </div>
    </div>
    <div class="ui blue labeled submit icon button" @click="replyComment">
      <i class="icon edit"></i> Add Reply
    </div>
  </form>
</div> <!-- /container -->
<script>
Vue.component("discuss-main-content", {
    template: "#discussMainContent",
    props: {
        thread_head: Object,
        content: Boolean,
        owner: String,
        id: String
    },
    data: function() {
        return {};
    },
    methods: {
        readTime: function(content) {
            return this.$root.readTime(content);
        }
    }
})
    window.discussTable = new Vue({
        el:".ui.container.padding",
        data:function(){
            var query_string = parseQueryString(window.location.hash.substring(1));
            return {
                page:parseInt(query_string.page)||0,
                table_val:{},
                total:0,
                id:getParameterByName("id")||0,
                replyText:"",
                captcha:"",
                owner:"",
                content:false,
                isadmin:false
            }
        },
        computed:{
            table:{
                get:function(){
                    return this.table_val;
                },
                set:function(val){
                    _.forEach(val,function(v,idx){
                        if(v && v.length) {
                            _.forEach(v,function(v,idx){
                        if(v.content) {
                            v.content = markdownIt.render(v.content);
                        }
                            })
                        }
                        
                    })
                    
                    this.table_val = val;
                    this.owner = val.owner;
                    this.isadmin = val.admin;
                }
            },
            thread_head:function(){
                var context = {
                    title:""
                };
                _.assign(context,this.table_val.discuss_header_content);
                if(context.content)
                    context.content = markdownIt.render(context.content);
                return context;
            },
            reply:function(){
                return this.table_val.discuss;
            }
        },
        created:function(){
            $(document).on("click", function() { $(".mermaid").each(function(el, v) { if($(v).is(":visible")) { mermaid.init(undefined, v)}}) });
        },
        beforeUpdate: function() {
            //console.time("update use time");
        },
        updated:function(){
                $content = $(".table-of-contents").html();
                $container = $("#contentContainer");
                if(!$content)$content = "";
                $(".table-of-contents").html("");
                if($content) {
                    $container.html("" + $content + "");
                }
                $("#sticky_content").sticky({
                    context: "#main_context",
                    offset: 50
                });
                this.content = $content && $content.html && $content.html().trim().length > 0 || ($container && $container.html().trim().length > 0);
                //console.timeEnd("update use time");
        },
        mounted:function(){
            var page = this.page * 20;
            var that = this;
            $.get("/api/discuss/"+this.id+"?page="+page,function(data){
                that.table = data;
                $.get("/api/discuss/"+that.id+"?page="+page,function(data){
                that.table = data;
            });
            });
            
            this.$nextTick(function(){
                var copy_content = new Clipboard(".copy.context",{
                        text: function (trigger) {
                            return $(trigger).parent().next().text();
                        }
                        });
                    copy_content.on("success",function(e){
                    $(e.trigger)
                    .popup({
                        title   : 'Finished',
                    content : 'Context is in your clipboard',
                        on      : 'click'
                     })
                     .popup("show");
                    })
            })
        },
        methods:{
            replyComment:function() {
                var send = {
                    comment:this.replyText,
                    captcha:this.captcha
                };
                var that = this;
                $.post("../api/discuss/reply/"+this.id,send,function(data){
                    if(data.status == "OK") {
                        alert("回复成功");
                        location.href = "/discusscontext.php?id="+that.id;
                    }
                    else {
                        alert("回复失败！服务器发生未知错误");
                    }
                })
            },
            block_reply:function(comment_id){
                $.get("../api/discuss/update/reply/block/"+this.id+"/"+comment_id,function(data){
                    if(data.status == "OK") {
                        alert("操作成功");
                    }
                    else {
                        alert("操作失败");
                    }
                })
            },
            readTime:function(content){
                var doc = document.createElement("div");
                doc.innerHTML = content;
                return parseInt(Math.ceil(doc.innerText.length / 300) ** 1.41428579532);
            }
        }
    })
</script>
    <?php include("template/semantic-ui/bottom.php") ?>
</body>
</html>
