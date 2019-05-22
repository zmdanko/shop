jQuery(function($){
	var processFile = '/assets/inc/process.php',
		confirmDelete = '/admin/confirmDelete.php',
		orders = '/admin/orders.php';
		fx = {
			/**
			 * 创建窗口
			 * @param  {str} data [窗口class]
			 * @return {obj}      [创建的窗口]
			 */
			"init" : function(data,to){
				if($("."+data+"").length==0){
					return $("<div>")
						.hide()
						.addClass(""+data+"")
						.appendTo(""+to+"");
				}else{
					return $("."+data+"");
				}
			},

			//创建模态窗口
			"initModal" : function(){
				return fx.init('modal_window','body');
			},

			//创建购物车窗口
			"initCart" : function(){
				return fx.init('cart_window','body');					
			},

			//创建模态覆盖
			"initOverlay" : function(){
				return fx.init('modal_overlay','body');
			},

			"cartOverlay" : function(){
				return fx.init('cart_overlay','body');
			},

			//刷新购物车
			"cartRefresh" : function(){
				var cart = fx.initCart();
				cart.load("/cart.php #content",function(){
					$(this)
						.children()
						.children()
						.unwrap();
				});
			},

			//显示购物车
			"cartIn" : function(refresh=false){
				var cart = fx.initCart();
					overlay = fx.cartOverlay();
				overlay
					.click(function(){
						fx.cartHide();
					});
				cart.hide();
				if(refresh==true){
					fx.cartRefresh();
				}			
				$(".cart_window,.cart_overlay")
					.fadeIn("slow");
			},

			//隐藏购物车
			"cartHide" : function(){
				$(".cart_window").hide();
				$(".cart_overlay")
					.fadeOut("slow",function(){
						$(this).remove();
					});
			},

			/**
			 * 数据加入模态窗口并显示
			 * @param  {obj} data [加入模态窗口的内容]
			 * @return {obj}      [模态窗口]
			 */
			"boxIn" : function(data){
				var modal = fx.initModal(),
					overlay = fx.initOverlay();
				overlay
					.click(function(){
						fx.boxOut();
					});
				modal
					.hide()
					.empty()
					.append(data)
					.appendTo("body");
				$(".modal_window,.modal_overlay")
					.fadeIn("slow");
			},

			//关闭和清除模态窗口
			"boxOut" : function(){
				$(".modal_window,.modal_overlay")
					.fadeOut("slow",function(){
						$(this).remove();
					});
			},

			/**
			 * 解析连接，在模态窗口内显示内容
			 * @param  {obj} event [连接]
			 * @return {obj}       [模态窗口]
			 */
			"modalPage" : function(event){
				var page = $(event)
						.attr("href"),
					data = $("<div>")
						.load(""+page+" form",function(){
							$(this).children().unwrap();
						});
				fx.boxIn(data);
			},

			/**
			 * 读取指定窗口内容
			 * @param  {obj} event  [a连接]
			 * @param  {obj} window [要改变的标签]
			 * @return {obj}        [description]
			 */
			"loadWindow" : function(event,data,window,back=null,except=null){
				var a = /<\/a>/;
				if(a.test(event)){
					var page = $(event)
						.attr("href");
				}else{
					page = event;
				}
				$(window)
					.children(":not("+except+")")
					.remove();
				$(window)	
					.load(""+page+" "+data+"",function(){
						$(this).children().children().unwrap();
						if(back){
							back();
						}
					});		
			},

			"changeWindow" : function(data,window){
				var content = $(data).children().unwrap();
				$(window)
					.empty()
					.append(content);
			},

			//返回主页
			"homepage" : function(){
				fx.loadWindow('/','#content','#content');
			},

			//确认购买窗口
			"checkOut" : function(event){
				fx.loadWindow(event,'#content','.cart_window',function(){
					$("<button>")
						.addClass('returnCart')
						.html('返回购物车')
						.click(function(){
							fx.cartIn(true);
						})
						.prependTo(".cart_window");
				});	
			},

			"valid" : function(event){
				var	input = $(event).siblings("input[id]"),
					notnull = /.+/,
					validInput = $.validInput(input,notnull),
					email = $(event).siblings("input[id=email]"),
					emailRegexp = /^.+@(.+\.)[A-Za-z]+$/,
					validEmail = $.validInput(email,emailRegexp);
				if(validInput.length!=0){
					$.each(validInput,function(index,content){
						$("input[id="+content+"]")
							.css('border-color','red')
							.attr('placeholder','该项不能为空')
							.click(function(){
								$(this).css('border-color','');
							});
					});
					return false;
				}
				if(validEmail.length!=0){
					$("input[id=email]")
						.after("<p id='alert'>请输入正确邮箱格式</p>");
					return false;
				}
			}
		};

	//首页登陆连接
	$("a[href='/identification/login.php']").on("click",function(){
		event.preventDefault();
		fx.modalPage(this);
	});

	//添加商品连接
	var href = ['/admin/orders','/admin/item'];
	$.each(href,function(index,content){
		$("a[href='"+content+".php']").on("click",function(){
			event.preventDefault();
			fx.loadWindow(this,'#content','#content');
		});
	});

	//模态窗口里的a连接
	$("body").on("click",'.modal_window a:not(:contains("取消"))',function(){
		event.preventDefault();
		fx.modalPage(this);
	});

	//input[type='text']的form表单
	var button = ['登陆','注册','发送更改密码邮件','保存地址','删除地址','确认'];
	$.each(button,function(index,content){
		$("body").on("click","form input[value="+content+"]",function(){
			event.preventDefault();
			if(fx.valid(this)!=false){
				var formData = $(this)
						.parents("form")
						.serialize();
				$.ajax({
					type:"POST",
					url:processFile,
					data:formData + "&ajax=TRUE",
					success:function(data){
						if(content=='登陆'){
							window.location.href=data;
							return;
						}
						if((content=='保存地址')||(content=='删除地址')){
							fx.checkOut(data);
							return;
						}
						if(content=='确认'){
							fx.boxIn(data);
							if(data=='保存商品成功'){
								fx.homepage();
							}			
							return;
						}
						if((data!='注册成功，请打开邮箱连接激活账号')||(data!='已发送密码更改连接，请在邮箱内查看')){
							$(".cart_window form").append("<p id='alert'>"+data+"</p>");
						}else{
							fx.boxIn(data);
						}
					},
					error:function(msg){
						alert(msg);
					}
				});
			}
		});
	});

	//
	$("body").on("click","input[value='支付']",function(){
		event.preventDefault();
		var formData = $(this)
			.parents("form")
			.serialize();
		$.ajax({
			type:"POST",
				url:processFile,
				data:formData + "&ajax=TRUE",
				success:function(data){
					if(data=='请选择地址'){
						fx.boxIn(data);
					}else{
						fx.checkOut(data);
					}
				},
				error:function(msg){
					alert(msg);
				}
		});
	});

	//取消input[type='text']的form表单验证提示
	$("body").on("click","input[id]",function(){
		$("#alert").remove();
	});

	//购物车连接
	$("a[href='/cart.php']").on("click",function(){
		event.preventDefault();
		if($(".cart_window").length==0){
			fx.cartIn(true);
		}else if($(".cart_window").css('display')=='none'){
			fx.cartIn();
		}else{
			fx.cartHide();
		}
	});

	//#content里的a连接
	$("#content").on("click","a:not(:contains('编辑'))",function(){
		event.preventDefault();
		fx.loadWindow(this,'#item','#item');
	});

	$("#content").on("click","a:contains('编辑')",function(){
		event.preventDefault();
		fx.loadWindow(this,'#content','#content');
	});

	//改变购物车数量的input
	var cartInput = ['加入购物车','保存更改'];
	$.each(cartInput,function(index,content){
		$("body").on('click',"input[value="+content+"]",function(){
			event.preventDefault();
			var input = $(this).parent().find("input[id]"),
				regexp = /\D/,
				valid = $.validInput(input,regexp),
				formData = $(this)
					.parents("form")
					.serialize();
			if(valid.length!=0){
				$.ajax({
					type: "POST",
					url: processFile,
					data: formData,
					success:function(data){
						fx.boxIn(data);
						if(($(".cart_window").length==0)||($(".cart_window").css('display')=='none')){
							fx.cartRefresh();
						}else{
							fx.cartIn(true);
						}
					},
					error:function(msg){
						alert(msg);
					}
				});
			}else{
				fx.boxIn('请输入正确数量');
			}
		});
	});

	//购物车内的a连接
	$("body").on("click",".cart_window a",function(){
		event.preventDefault();
		fx.checkOut(this);
	});

	//删除input
	var category = ['删除','删除商品','确认删除'];
	$.each(category,function(index,content){
		$('body').on('click',"input[value='"+content+"']",function(){
			event.preventDefault();
			var formData = $(this)
				.parent('form')
				.serialize();
			if($(this).siblings('input[name="picture"]').val()!=''){
				var homepage = false;
			}
			$.ajax({
				type: 'POST',
				url: confirmDelete,
				data: formData,
				success:function(data){
					if((content=='删除')||(content=='删除商品')){
						form = $(data)
							.children("form");
						fx.boxIn(form);
					}
					if(content=='确认删除'){
						if(homepage==false){
							var item = $('input[name="item_id"]').val();
							href = '/admin/item.php?item='+item;
							fx.loadWindow(href,'#content','#content');
						}else{
							fx.homepage();		
						}
						fx.boxOut();
					}
				},
				error:function(msg){
					alert(msg);
				}
			});
		});
	});

	//上传商品图片
	$('body').on('click','input[value="上传"]',function(){
		event.preventDefault();
		var formData = new FormData($(this).parents('form')[0]);
		formData.append('ajax','TURE');
		$.ajax({
			type: 'POST',
			url: processFile,
			data: formData,
			processData: false,
			contentType: false,
			success: function(data){
				regexp = /item=(\d)/;
				if(data.match(regexp)){
					fx.loadWindow(data,'#content','#content');
				}else{
					fx.boxIn(data);
				}	
			},
			error: function(msg){
				alert(msg);
			}
		});
	})

	$('body').on('click','a:contains("取消")',function(){
		event.preventDefault();
		fx.boxOut();
	});

	$('body').on('click','input[value="查询"]',function(){
		event.preventDefault();
		var formData = new FormData(),
			condition = $(this)
			.siblings('select')
			.val();
			
		formData.append('condition',condition);
		$.ajax({
			type: 'POST',
			url: orders,
			data: formData,
			processData: false,
			contentType: false,
			success: function(data){
				content = $(data).siblings('#content');
				fx.changeWindow(content,'#content');
				$('option').removeAttr('selected');
				$("option[value="+condition+"]").attr('selected','selected');
			},
			error: function(msg){
				alert(msg);
			}
		});
	})
});