# FileHandler Trait

remember when I told you that the [`BaseRepository`](base-repository.md#baserepository-class) handle your files columns
automatically without the need from you to do that well it uses this trait to do that and so as you can.

this trait provides the following methods which you can use :

`storeFile($file, $dir, $to_compress = true, $is_base_64 = false, $width = 300)`: This method takes a file, directory
path, and optional parameters, and stores the file in the specified directory. If the file is a base64-encoded image, it
generates a unique name for the file based on the current timestamp. If compression is enabled, it resizes the image to
the specified width while maintaining the aspect ratio. Finally, it returns the name of the stored file.

`updateFile($new_file, $old_file, $dir, $to_compress = true, $is_base_64 = false, $width = 300)`: This method is similar
to storeFile, but it also takes an old file name as a parameter. It deletes the old file and then calls storeFile to
store the new file. It returns the name of the new file.

`deleteFile($file)`: This method takes a file name and deletes it from the filesystem. It returns true if the file was
deleted successfully and false if the file was not found.

`storeNormalFile($key)`: This method is used to store any type of file, not just images. It takes a request key and
stores
the file in the public storage directory. It generates a unique file name based on the current timestamp and returns the
generated name.

`storeOrUpdateRequestedFiles(array $data, array $filesKeys = [], bool $is_store = true, $item = null, $to_compress =
true, $is_base_64 = false, $width = 300)`: This method is a utility method that is used to store or update multiple
files
based on the provided data and file keys. It takes an array of data, an array of file keys, and optional parameters. It
iterates over the file keys and checks if the key exists in the data array. If it does, it calls either storeFile or
updateFile based on the $is_store parameter. It removes the file key from the data array and merges the generated file
name into the data array. Finally, it returns the updated data array.

`storeImageFromUrl($url, $dir)`: This method takes a URL and a directory path, creates the directory if it doesn't
exist,
generates a random name for the image, and saves the image from the URL in the specified directory. It returns an array
containing the name of the stored image and the image object.

Overall, this trait provides convenient methods for storing, updating, and deleting files, with specific support for
image handling, in a Laravel application.
