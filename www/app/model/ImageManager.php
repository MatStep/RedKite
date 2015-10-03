<?php

namespace App\Model;

use Nette,
	Nette\Utils\Strings,
	Nette\Utils\Image,
	Nette\Security\Passwords;


/**
 * Image management.
 */
class ImageManager extends Nette\Object
{
	const 
		IMAGE_RESIZE_WIDTH = 1000;

	public function getImage($file, $imageFor)
	{
		$imageName = $file->name;

        $imgUrl = 'images/' . $imageFor . '/' . $imageName;

        $fileSize = $file->getSize();
        \Tracy\Debugger::log($fileSize);

        $file->move($imgUrl);
        //convert uploaded file to image class
        $file = $file->toImage();

        if ( $fileSize > 500 * 1024)
        {
            //if image is wider than taller and width is more than max width
	        if ( $file->getWidth() >= $file->getHeight() && $file->getWidth() > self::IMAGE_RESIZE_WIDTH)
	        {
		        $file->resize(self::IMAGE_RESIZE_WIDTH, NULL);
		    }
		    //else height is more than max width
		    else if ( $file->getHeight() > 1000 )
		    {
		    	$file->resize(NULL, self::IMAGE_RESIZE_WIDTH);
		    }
		    //if the image isnt larger than max width then just resize it to make it smaller
		    else 
		    {
		    	$file->resize($file->getWidth(), $file->getHeight());
		    }
		}
	    //overwrite the previous image with the edited one
	    $file->save($imgUrl);
	    //return the image URL
	    return $imgUrl;
	}

	public function getExtension($fileName)
	{
		//initialise variable extension
		$extension = "";
		//go from the back of the string until a dot has been found
		for ( $i = strlen($fileName) - 1; $fileName[$i] != '.'; $i-- )
		{
			//add the last chars of the filename to the extension
			$extension = $fileName[$i] . $extension;
		}
		//add a dot to the extension
		$extension = "." . $extension;
		//return the extension
		return $extension;
	}
}