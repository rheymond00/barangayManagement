$(document).ready(function(){
    $("#current_pwd").keyup(function(){
        var current_pwd = $("#current_pwd").val();
        // alert(current_pwd);
        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            type:'post',
            url:'/admin/check-current-password',
            data:{current_pwd:current_pwd},
            success:function(resp){
                if(resp=="false"){
                    $("#verifyCurrentPwd").html("Current Password is Incorrect!");
                }else if(resp=="true"){
                    $("#verifyCurrentPwd").html("Current Password is Correct!");
                }

            },error:function(){
                alert("Error");
            }
        });
    });
    // Update CMS Page Status
    $(document).on("click",".updateCmsPageStatus",function(){
        var status = $(this).children("i").attr("status");
        var page_id = $(this).attr("page_id");
        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            type: 'post',
            url: '/admin/update-cms-page-status',
            data: { status: status, page_id: page_id },
            success: function(resp) {
                if (resp['status'] == 0) {
                    $("#page-" + page_id).html("<i class='fas fa-toggle-off' style='color:grey' status='Inactive'></i>");
                } else if (resp['status'] == 1) {
                    $("#page-" + page_id).html("<i class='fas fa-toggle-on' style='color:#3f6ed3' status='Active'></i>");
                }
            },
            error: function() {
                alert("Error");
            }
        });
        
    })

    // Update subadmin status
    $(document).on("click",".updateSubadminStatus",function(){
        var status = $(this).children("i").attr("status");
        var subadmin_id = $(this).attr("id").split("-")[1]; // Extract subadmin_id from the id attribute
        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            type: 'post',
            url: '/admin/update-subadmin-status',
            data: { status: status, subadmin_id: subadmin_id},
            success: function(resp) {
                if (resp['status'] == 0) {
                    $("#subadmin-" + subadmin_id).html("<i class='fas fa-toggle-off' style='color:grey' status='Inactive'></i>");
                } else if (resp['status'] == 1) {
                    $("#subadmin-" + subadmin_id).html("<i class='fas fa-toggle-on' style='color:#3f6ed3' status='Active'></i>");
                }
            },
            error: function() {
                alert("Error");
            }
        });
    });
    
        //confirm the deletion of cms page
    $(document).on("click",".confirmDelete",function(){

        var name = $(this).attr('name');
        if(confirm('Are you sure to delete this '+name+'?')){
            return true;
        }
        return false;
    });
});


