<!DOCTYPE html>
<html>
<head>
    <title>Web-Cam Recorder</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.1.3/css/bootstrap.min.css" />
    <link href="https://cdn.datatables.net/1.10.16/css/jquery.dataTables.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.10.19/css/dataTables.bootstrap4.min.css" rel="stylesheet">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.js"></script>  
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.0/jquery.validate.js"></script>
    <script src="https://cdn.datatables.net/1.10.16/js/jquery.dataTables.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.19/js/dataTables.bootstrap4.min.js"></script>
</head>
<body class="my-3 mx-3 ">
      
<div class="text-center">
    <h1 class="bg-primary py-3 text-white">Web-Cam Recorder Video</h1>
    <!-- web camera -->
    <button type="button" class="btn btn-outline-primary" onclick="startFunction()">Start Video Recording</button>
    <button type="button" class="btn btn-outline-secondary" onclick="download()">Stop video & Download</button>
    <div class="py-3 px-3 bg-info text-white" id="msgbox"></div>
    <p><video id="video" autoplay width=320/><p>
    <a class="btn btn-lg btn-success" href="javascript:void(0)" id="createNewVideo"> Add New Video</a>
</div>
   <div class="border-top pt-3">
    <table class="table table-bordered data-table">
        <thead>
            <tr>
                <th>No</th>
                <th>Title</th>
                <th>Name</th>
                <th>Video</th>
                <th width="280px">Action</th>
            </tr>
        </thead>
        <tbody>
        </tbody>
    </table>
</div>
     
<div class="modal fade" id="ajaxModel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="modelHeading"></h4>
            </div>
            <div class="modal-body">
           
                <form id="videoForm" name="videoForm" class="form-horizontal" enctype="multipart/form-data">
                   <input type="hidden" name="videoId" id="videoId">
                    <div class="form-group">
                        <label for="title" class="col-sm-2 control-label">Title</label>
                        <div class="col-sm-12">
                            <input type="text" class="form-control" id="title" name="title" placeholder="Enter Title" value="" maxlength="50" required="">
                        </div>
                    </div>
                   
                    <div class="form-group">
                        <label for="name" class="col-sm-2 control-label">Name</label>
                        <div class="col-sm-12">
                            <input type="text" class="form-control" id="name" name="name" placeholder="Enter Name" value="" maxlength="50" required="">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-2 control-label">Video</label>
                        <div class="col-sm-12">
                        <input type="file" class="form-control" id="videocam" name="videocam" placeholder="Enter Video" value="" maxlength="50" required="">
                        </div>
                    </div>
        
                    <div class="col-sm-offset-2 col-sm-10">
                     <button type="submit" class="btn btn-primary" id="saveBtn" value="create">Save changes
                     </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
      
</body>
      
<script type="text/javascript">
  ///////////////////////  webcam
  $('#msgbox').hide();
  function startFunction(){
  var recordedChunks = [];
  var video = document.querySelector('#video');
    if(navigator.mediaDevices.getUserMedia){
      navigator.mediaDevices.getUserMedia({video: true})
    .then(function(stream){
        video.srcObject = stream;
        try {
        var recorder = new MediaRecorder(stream, {mimeType : "video/webm"});
        } catch (e) {
          console.error('Exception while creating MediaRecorder: ' + e);
          return;
        }
      var theRecorder = recorder;
        recorder.ondataavailable = 
            (event) => { recordedChunks.push(event.data); };
        recorder.start(100);
      })
    .catch(function(error){
    console.log('error');
  })
 }
} 
function download() {
  var recordedChunks = [];
  var blob = new Blob(recordedChunks, {type: "video/webm"});
  var url =  URL.createObjectURL(blob);
  var a = document.createElement("a");
  document.body.appendChild(a);
  a.style = "display: block";
  a.href = url;
  a.download = 'test.webm';
  a.click();
  // setTimeout() here is needed for Firefox.
  setTimeout(function() { URL.revokeObjectURL(url); }, 100); 
}
  ///////////////////////end webcam
  $(function () {
      
    /*------------ Pass Header Token  --------------*/ 
    $.ajaxSetup({
          headers: {
              'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
          }
    });
      
    /*------------Render DataTable-------------*/
    var table = $('.data-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: "{{ route('video.index') }}",
        columns: [
          {data: 'DT_RowIndex', name: 'DT_RowIndex'},
            {data: 'title', name: 'title'},
            {data: 'name', name: 'name'},
            {data: 'video', name: 'video'},
            {data: 'action', name: 'action', orderable: false, searchable: false},
        ]
    });
      
    /*------------Click to Button-------------*/
    $('#createNewVideo').click(function () {
        $('#saveBtn').val("create-video");
        $('#videoId').val('');
        $('#videoForm').trigger("reset");
        $('#modelHeading').html("Add New Video");
        $('#ajaxModel').modal('show');
    });
      
    /*----------Click to Edit Button-------------*/
    $('body').on('click', '.editvideo', function () {
      var videoId = $(this).data('id');
      $.get("{{ route('video.index') }}" +'/' + videoId +'/edit', function (data) {
          $('#modelHeading').html("Edit Video");
          $('#saveBtn').val("edit-video");
          $('#ajaxModel').modal('show');
          $('#videoId').val(data.id);
          $('#title').val(data.title);
          $('#name').val(data.name);
          $('#videoweb').val(data.video);
      })
    });
      
    /*----------Create Video Code----------*/
    $('#saveBtn').click(function (e) {
        e.preventDefault();
        var videoId = $('#videoId').val();
        var title = $('#title').val();
        var name = $('#name').val();
        var video = $('#videocam')[0].files;
        var fd = new FormData();

        // Append data 
        fd.append('videoId',videoId);
        fd.append('title',title);
        fd.append('name',name);
        fd.append('video',video[0]);
        $.ajax({
          processData: false,
          contentType: false,
          data: fd,
          url: "{{ route('video.store') }}",
          type: "POST",
          enctype: 'multipart/form-data',
          dataType: 'json',
          success: function (data) {
            $('#msgbox').show();
                $('#msgbox').html(data.message);
                $('#msgbox').hide(10000);
              $('#videoForm').trigger("reset");
              $('#ajaxModel').modal('hide');
              table.draw();
           
          },
          error: function (data) {
              console.log('Error:', data);
              $('#saveBtn').html('Save Changes');
          }
      });
    });
      
    /*--------Delete Video Code--------------*/
    $('body').on('click', '.deleteVideo', function () {
     
        var id = $(this).data("id");
        confirm("Are You sure want to delete !");
        
        $.ajax({
            type: "DELETE",
            url: "{{ route('video.store') }}"+'/'+id,
            success: function (data) {
                table.draw();
            },
            error: function (data) {
                console.log('Error:', data);
            }
        });
    });
       
  });
</script>
</html>