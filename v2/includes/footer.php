<footer class="main-footer" style="text-align: center;">
    <!-- <div class="pull-right hidden-xs">
      /../
    </div> -->
    <strong>Powered By</strong> Compresol.
  </footer>

</div>
<!-- ./wrapper -->


<script>
  Pace.on("start", function(){
    //$("div.paceDiv").css("visibility","visible")
    $("div.paceDiv").css("background-color","rgba(255,255,255,1.0)")
  });

  Pace.on("done", function(){
    //$("div.paceDiv").css("display","none")
    $("div.paceDiv").css("visibility","hidden");
    $("div.paceDiv").css("background-color","rgba(255,255,255,0.0)")
  });
  $(document).ready(function(){
	 
	$("div.paceDiv").css("background-color","rgba(255,255,255,0.0)");
	$("div.paceDiv").css("visibility","hidden");
    
	$(".dropdownmenu").on("click",function(){

		if(!$(this).hasClass("menu-open")){
			$(".dropdownmenu").each(function(){
				$(this).removeClass("menu-open");
				$(this).find("ul").removeClass("display-block");
			});
		}

		$(this).find("ul").toggleClass("display-block");
		$(this).toggleClass("menu-open");

    });


    $(".dropdownmenu").each(function(){
      let liCount = 0;
      $(this).find("ul").find("li").each(function(){
        liCount++;
      });

      if(liCount == 0)
        $(this).css("display","none");
      
    });
	
	$(".moduleabc").each(function(){
      let liCount = 0;
      $(this).find(".linkabcd").each(function(){
        liCount++;
      });

      if(liCount == 0)
        $(this).css("display","none");
      
    });

  });
</script>
