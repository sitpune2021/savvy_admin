<form action="{{ route('image.upload') }}" method="POST" enctype="multipart/form-data">
    @csrf
    <input type="file" name="image" required>
    <button type="submit">Upload Image</button>
</form>
@if(session('path'))
    <img src="{{ asset('storage/' . session('path')) }}" alt="Uploaded Image" style="max-width:300px;">
@endif
