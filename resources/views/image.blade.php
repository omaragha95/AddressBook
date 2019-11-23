<form action="/upload" method="post" enctype="multipart/form-data">
    {{ csrf_field() }}

    <input type="file" name="image[]" multiple>
    <input type="submit" value="Upload">

</form>