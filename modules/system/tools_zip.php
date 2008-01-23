<?php
/*
	Copyright (c) 2002-2007 Netlor
	Copyright (c) 2007-2008 Ovensia
	Contributors hold Copyright (c) to their code submissions.

	This file is part of Ploopi.

	Ploopi is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation; either version 2 of the License, or
	(at your option) any later version.

	Ploopi is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with Ploopi; if not, write to the Free Software
	Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/
?>
<?
ob_end_clean();

// ----- Constants
define( 'PCLZIP_READ_BLOCK_SIZE', 2048 );

class zip
{
	// ----- Filename of the zip file
	var $zipname = '';
	
	// ----- File descriptor of the zip file
	var $zip_fd = 0;
	
	
		
	function zip($p_zipname)
	{
		// ----- Tests the zlib
		if (!function_exists('gzopen'))
		{
		ploopi_die('Abort '.basename(__FILE__).' : Missing zlib extensions');
		}
		
		// ----- Set the attributes
		$this->zipname = $p_zipname;
		$this->zip_fd = 0;
		
		// ----- return
		return;
	}
	
	
	function create($p_filelist, $p_add_dir="", $p_remove_dir="")
	{
		$v_result=1;
		
		// ----- Look if the $p_filelist is really an array
		$p_result_list = array();
		if (is_array($p_filelist))
		{
			// ----- Call the create fct
			$v_result = $this->privCreate($p_filelist, $p_result_list, $p_add_dir, $p_remove_dir);
		}
		
		// ----- Look if the $p_filelist is a string
		else if (is_string($p_filelist))
		{
			// ----- Create a list with the elements from the string
			$v_list = explode(" ", $p_filelist);
			
			// ----- Call the create fct
			$v_result = $this->privCreate($v_list, $p_result_list, $p_add_dir, $p_remove_dir);
		}
		
		// ----- Invalid variable
		else
		{
			// ----- Error log
			$v_result = -3;
		}
		
		if ($v_result != 1)
		{
			// ----- return
			return 0;
		}
		
		// ----- return
		return $p_result_list;
	}
	// --------------------------------------------------------------------------------
	
	function add($p_filelist, $p_add_dir="", $p_remove_dir="")
	{
		$v_result=1;
		
		// ----- Look if the $p_filelist is really an array
		$p_result_list = array();

		if (is_array($p_filelist))
		{
			// ----- Call the create fct
			$v_result = $this->privAdd($p_filelist, $p_result_list, $p_add_dir, $p_remove_dir);
		}
		
		// ----- Look if the $p_filelist is a string
		else if (is_string($p_filelist))
		{
			// ----- Create a list with the elements from the string
			$v_list = explode(" ", $p_filelist);
			
			// ----- Call the create fct
			$v_result = $this->privAdd($v_list, $p_result_list, $p_add_dir, $p_remove_dir);
		}
		
		// ----- Invalid variable
		else
		{
			// ----- Error log
			$v_result = -3;
		}
		
		if ($v_result != 1)
		{
			// ----- return
			return 0;
		}
		
		// ----- return
		return $p_result_list;
	}

	function privCreate($p_list, &$p_result_list, $p_add_dir="", $p_remove_dir="")
	{
		$v_result=1;
		$v_list_detail = array();
		
		$v_result = $this->privOpenFd('wb');
		
		// ----- Add the list of files
		$v_result = $this->privAddList($p_list, $p_result_list, $p_add_dir, $p_remove_dir);
		
		$v_result = $this->privCloseFd();
	
		// ----- return
		return $v_result;
	}


	function privAddList($p_list, &$p_result_list, $p_add_dir, $p_remove_dir)
	{
		$v_result=1;
		
		// ----- Add the files
		$v_header_list = array();

		if (($v_result = $this->privAddFileList($p_list, $v_header_list, $p_add_dir, $p_remove_dir)) != 1)
		{
			// ----- return
			return $v_result;
		}
		
		// ----- Store the offset of the central dir
		$v_offset = @ftell($this->zip_fd);
		
		// ----- Create the Central Dir files header
		for ($i=0; $i<sizeof($v_header_list); $i++)
		{
			// ----- Create the file header
			if (($v_result = $this->privWriteCentralFileHeader($v_header_list[$i])) != 1)
			{
				// ----- return
				return $v_result;
			}
		
			// ----- Transform the header to a 'usable' info
			$this->privConvertHeader2FileInfo($v_header_list[$i], $p_result_list[$i]);
		}
		
		// ----- Zip file comment
		$v_comment = '';
		
		// ----- Calculate the size of the central header
		$v_size = @ftell($this->zip_fd)-$v_offset;
		
		// ----- Create the central dir footer
		if (($v_result = $this->privWriteCentralHeader(sizeof($v_header_list), $v_size, $v_offset, $v_comment)) != 1)
		{
			// ----- Reset the file list
			unset($v_header_list);
			return $v_result;
		}
		
		return $v_result;
	}

	function privAddFileList($p_list, &$p_result_list, $p_add_dir, $p_remove_dir)
	{

		$v_result=1;
		$v_header = array();
		
		// ----- Recuperate the current number of elt in list
		$v_nb = sizeof($p_result_list);
		
		// ----- Loop on the files
		for ($j=0; ($j<count($p_list)) && ($v_result==1); $j++)
		{
			// ----- Recuperate the filename
			$p_filename = $p_list[$j];
			
			
			// ----- Skip empty file names
			if ($p_filename == "")
			{
				continue;
			}
		
			/*
			if (!$this->isAuthorized($p_filename))
			{
				return -4;
			}
			*/
			
			// ----- Check the filename
			if (!file_exists($p_filename))
			{
				return -4;
			}
		
			// ----- Check the path length
			if (strlen($p_filename) > 0xFF)
			{
				return -5;
			}
		
			
			// ----- Add the file
			if (($v_result = $this->privAddFile($p_filename, $v_header, $p_add_dir, $p_remove_dir)) != 1)
			{
				return $v_result;
			}
		
			// ----- Store the file infos
			$p_result_list[$v_nb++] = $v_header;
			
			// ----- Look for directory
			if (is_dir($p_filename))
			{
		
				// ----- Look for path
				if ($p_filename != ".") $v_path = $p_filename."/";
				else $v_path = "";
				
				
				// ----- Read the directory for files and sub-directories
				$p_hdir = opendir($p_filename);
				$p_hitem = readdir($p_hdir); // '.' directory
				$p_hitem = readdir($p_hdir); // '..' directory
				while ($p_hitem = readdir($p_hdir))
				{
		
					// ----- Look for a file
					
					if (substr($v_path.$p_hitem,'-4') != '.tmp' && $v_path.$p_hitem != $this->zipname)
					{
						if (is_file($v_path.$p_hitem))
						{
			
							// ----- Add the file
							if (($v_result = $this->privAddFile($v_path.$p_hitem, $v_header, $p_add_dir, $p_remove_dir)) != 1)
							{
								// ----- return status
								return $v_result;
							}
			
							// ----- Store the file infos
							$p_result_list[$v_nb++] = $v_header;
						}
			
						// ----- Recursive call to PclTarHandleAddFile()
						else
						{
							
							// ----- Need an array as parameter
							$p_temp_list[0] = $v_path.$p_hitem;
							$v_result = $this->privAddFileList($p_temp_list, $p_result_list, $p_add_dir, $p_remove_dir);
							
							// ----- Update the number of elements of the list
							$v_nb = sizeof($p_result_list);
						}
					}
				}

			// ----- Free memory for the recursive loop
			unset($p_temp_list);
			unset($p_hdir);
			unset($p_hitem);
			}
		}
		
		return $v_result;
	}
	
	function privWriteCentralHeader($p_nb_entries, $p_size, $p_offset, $p_comment)
	{
		$v_result=1;
		
		// ----- Packed data
		$v_binary_data = pack("VvvvvVVv", 0x06054b50, 0, 0, 0, $p_nb_entries, $p_size, $p_offset, strlen($p_comment));
		
		// ----- Write the 22 bytes of the header in the zip file
		fputs($this->zip_fd, $v_binary_data, 22);
		
		// ----- Write the variable fields
		if (strlen($p_comment) != 0)
		{
			fputs($this->zip_fd, $p_comment, strlen($p_comment));
		}
		
		return $v_result;
	}
	
	function privAdd($p_list, &$p_result_list, $p_add_dir="", $p_remove_dir="")
	{
		$v_result=1;
		$v_list_detail = array();
		

		// ----- Look if the archive exists
		if (!is_file($this->zipname))
		{
			// ----- Do a create
			$v_result = $this->privCreate($p_list, $p_result_list, $p_add_dir, $p_remove_dir);
			// ----- return
			return $v_result;
		}
		
		// ----- Open the zip file
		if (($v_result=$this->privOpenFd('rb')) != 1)
		{
			return $v_result;
		}
		
		// ----- Read the central directory informations
		$v_central_dir = array();
		if (($v_result = $this->privReadEndCentralDir($v_central_dir)) != 1)
		{
			$this->privCloseFd();
			return $v_result;
		}
		
		// ----- Go to beginning of File
		@rewind($this->zip_fd);
		
		// ----- Creates a temporay file
		$v_zip_temp_name = uniqid('/tmp/pclzip-').'.tmp';
		
		// ----- Open the temporary file in write mode
		if (($v_zip_temp_fd = @fopen($v_zip_temp_name, 'wb')) == 0)
		{
			$this->privCloseFd();
			return -2;
		}
		
		// ----- Copy the files from the archive to the temporary file
		// TBC : Here I should better append the file and go back to erase the central dir
		$v_size = $v_central_dir['offset'];
		while ($v_size != 0)
		{
			$v_read_size = ($v_size < PCLZIP_READ_BLOCK_SIZE ? $v_size : PCLZIP_READ_BLOCK_SIZE);
			$v_buffer = fread($this->zip_fd, $v_read_size);
			@fwrite($v_zip_temp_fd, $v_buffer, $v_read_size);
			$v_size -= $v_read_size;
		}
		
		// ----- Swap the file descriptor
		// Here is a trick : I swap the temporary fd with the zip fd, in order to use
		// the following methods on the temporary fil and not the real archive
		$v_swap = $this->zip_fd;
		$this->zip_fd = $v_zip_temp_fd;
		$v_zip_temp_fd = $v_swap;
		
		// ----- Add the files
		$v_header_list = array();
		if (($v_result = $this->privAddFileList($p_list, $v_header_list, $p_add_dir, $p_remove_dir)) != 1)
		{
			fclose($v_zip_temp_fd);
			$this->privCloseFd();
			@unlink($v_zip_temp_name);
		
			// ----- return
			return $v_result;
		}
		
		// ----- Store the offset of the central dir
		$v_offset = @ftell($this->zip_fd);
		
		// ----- Copy the block of file headers from the old archive
		$v_size = $v_central_dir['size'];
		while ($v_size != 0)
		{
			$v_read_size = ($v_size < PCLZIP_READ_BLOCK_SIZE ? $v_size : PCLZIP_READ_BLOCK_SIZE);
			$v_buffer = @fread($v_zip_temp_fd, $v_read_size);
			@fwrite($this->zip_fd, $v_buffer, $v_read_size);
			$v_size -= $v_read_size;
		}
		
		// ----- Create the Central Dir files header
		for ($i=0; $i<sizeof($v_header_list); $i++)
		{
			// ----- Create the file header
			if (($v_result = $this->privWriteCentralFileHeader($v_header_list[$i])) != 1)
			{
				fclose($v_zip_temp_fd);
				$this->privCloseFd();
				@unlink($v_zip_temp_name);
				
				// ----- return
				return $v_result;
			}
		
			// ----- Transform the header to a 'usable' info
			$this->privConvertHeader2FileInfo($v_header_list[$i], $p_result_list[$i]);
		}
		
		// ----- Zip file comment
		$v_comment = '';
		
		// ----- Calculate the size of the central header
		$v_size = @ftell($this->zip_fd)-$v_offset;
		
		// ----- Create the central dir footer
		if (($v_result = $this->privWriteCentralHeader(sizeof($v_header_list)+$v_central_dir['entries'], $v_size, $v_offset, $v_comment)) != 1)
		{
			// ----- Reset the file list
			unset($v_header_list);
			// ----- return
			return $v_result;
		}
		
		// ----- Swap back the file descriptor
		$v_swap = $this->zip_fd;
		$this->zip_fd = $v_zip_temp_fd;
		$v_zip_temp_fd = $v_swap;
		
		// ----- Close
		$this->privCloseFd();
		
		// ----- Close the temporary file
		@fclose($v_zip_temp_fd);
		
		// ----- Delete the zip file
		// TBC : I should test the result ...
		@unlink($this->zipname);
		
		// ----- Rename the temporary file
		// TBC : I should test the result ...
		@rename($v_zip_temp_name, $this->zipname);
		
		// ----- return
		return $v_result;
	}

	function privOpenFd($p_mode)
	{
		$v_result=1;
		
		// ----- Look if already open
		if ($this->zip_fd != 0)
		{
			return -2;
		}
		
		// ----- Open the zip file
		if (($this->zip_fd = @fopen($this->zipname, $p_mode)) == 0)
		{
			return -2;
		}

		return $v_result;
	}

	function privCloseFd()
	{
		$v_result=1;
		if ($this->zip_fd != 0) @fclose($this->zip_fd);
		$this->zip_fd = 0;
		return $v_result;
	}
  
	function privReadEndCentralDir(&$p_central_dir)
	{
		$v_result=1;
		
		// ----- Go to the end of the zip file
		$v_size = filesize($this->zipname);
		@fseek($this->zip_fd, $v_size);
		if (@ftell($this->zip_fd) != $v_size)
		{
			return -10;
		}
		
		// ----- Go back to the maximum possible size of the Central Dir End Record
		$v_maximum_size = 277; // 0xFF + 22;
		if ($v_maximum_size > $v_size)
		$v_maximum_size = $v_size;
		@fseek($this->zip_fd, $v_size-$v_maximum_size);
		if (@ftell($this->zip_fd) != ($v_size-$v_maximum_size))
		{
			return -10;
		}
		
		// ----- Read byte per byte in order to find the signature
		$v_pos = ftell($this->zip_fd);
		$v_bytes = 0x00000000;
		while ($v_pos < $v_size)
		{
			// ----- Read a byte
			$v_byte = @fread($this->zip_fd, 1);
			
			// -----  Add the byte
			$v_bytes = ($v_bytes << 8) | Ord($v_byte);
			
			// ----- Compare the bytes
			if ($v_bytes == 0x504b0506)
			{
				$v_pos++;
				break;
			}
		
			$v_pos++;
		}
		
		// ----- Look if not found end of central dir
		if ($v_pos == $v_size)
		{
			return 0;
		}
		
		// ----- Read the first 18 bytes of the header
		$v_binary_data = fread($this->zip_fd, 18);
		
		// ----- Look for invalid block size
		if (strlen($v_binary_data) != 18)
		{
			return 0;
		}
		
		$v_data = unpack('vdisk/vdisk_start/vdisk_entries/ventries/Vsize/Voffset/vcomment_size', $v_binary_data);
		
		// ----- Check the global size
		if (($v_pos + $v_data['comment_size'] + 18) != $v_size)
		{
			return -10;
		}
		
		// ----- Get comment
		if ($v_data['comment_size'] != 0) $p_central_dir['comment'] = fread($this->zip_fd, $v_data['comment_size']);
		else $p_central_dir['comment'] = '';
		
		$p_central_dir['entries'] = $v_data['entries'];
		$p_central_dir['disk_entries'] = $v_data['disk_entries'];
		$p_central_dir['offset'] = $v_data['offset'];
		$p_central_dir['size'] = $v_data['size'];
		$p_central_dir['disk'] = $v_data['disk'];
		$p_central_dir['disk_start'] = $v_data['disk_start'];
		
		return $v_result;
	}
	
	
	function privAddFile($p_filename, &$p_header, $p_add_dir, $p_remove_dir)
	{
		$v_result=1;

		if ($p_filename == "")
		{
			return -3;
		}
		
		if ($this->isAuthorized($p_filename))
		{
			//echo "<BR>$p_filename";
			
			// ----- Calculate the stored filename
			$v_stored_filename = $p_filename;
			if ($p_remove_dir != "")
			{
				if (substr($p_remove_dir, -1) != '/') $p_remove_dir .= "/";
			
				if ((substr($p_filename, 0, 2) == "./") || (substr($p_remove_dir, 0, 2) == "./"))
				{
					if ((substr($p_filename, 0, 2) == "./") && (substr($p_remove_dir, 0, 2) != "./")) $p_remove_dir = "./".$p_remove_dir;
					if ((substr($p_filename, 0, 2) != "./") && (substr($p_remove_dir, 0, 2) == "./")) $p_remove_dir = substr($p_remove_dir, 2);
				}
				
				if (substr($p_filename, 0, strlen($p_remove_dir)) == $p_remove_dir)
				{
					$v_stored_filename = substr($p_filename, strlen($p_remove_dir));
				}
			}
			if ($p_add_dir != "")
			{
				if (substr($p_add_dir, -1) == "/") $v_stored_filename = $p_add_dir.$v_stored_filename;
				else $v_stored_filename = $p_add_dir."/".$v_stored_filename;
			}
			
			// ----- Filename (reduce the path of stored name)
			$v_stored_filename = PclZipUtilPathReduction($v_stored_filename);
			
	
			// ----- Check the path length
			if (strlen($v_stored_filename) > 0xFF)
			{
				return -5;
			}
			
			// ----- Set the file properties
			clearstatcache();
			$p_header['version'] = 20;
			$p_header['version_extracted'] = 10;
			$p_header['flag'] = 0;
			$p_header['compression'] = 0;
			$p_header['mtime'] = filemtime($p_filename);
			$p_header['crc'] = 0;
			$p_header['compressed_size'] = 0;
			$p_header['size'] = filesize($p_filename);
			$p_header['filename_len'] = strlen($p_filename);
			$p_header['extra_len'] = 0;
			$p_header['comment_len'] = 0;
			$p_header['disk'] = 0;
			$p_header['internal'] = 0;
			$p_header['external'] = 0xFE49FFE0;    // Value for a file : To be checked
			$p_header['offset'] = 0;
			$p_header['filename'] = $p_filename;
			$p_header['stored_filename'] = $v_stored_filename;
			$p_header['extra'] = '';
			$p_header['comment'] = '';
			$p_header['status'] = 'ok';
			
			// ----- Look for a file
			if (is_file($p_filename))
			{
				// ----- Open the source file
				if (($v_file = @fopen($p_filename, "rb")) == 0)
				{
					return -2;
				}
				
				$p_filename_compressed = '/tmp/temp';
				
				// ----- Creates a compressed temporary file
				if (($v_file_compressed = @gzopen($p_filename_compressed.'.gz', "wb")) == 0)
				{
					
					// ----- Close the file
					fclose($v_file);
					return -1;
				}
			
				// ----- Read the file by PCLZIP_READ_BLOCK_SIZE octets blocks
				$v_size = filesize($p_filename);
				while ($v_size != 0)
				{
					$v_read_size = ($v_size < PCLZIP_READ_BLOCK_SIZE ? $v_size : PCLZIP_READ_BLOCK_SIZE);
					$v_buffer = fread($v_file, $v_read_size);
					$v_binary_data = pack('a'.$v_read_size, $v_buffer);
					@gzputs($v_file_compressed, $v_binary_data, $v_read_size);
					$v_size -= $v_read_size;
				}
			
				// ----- Close the file
				@fclose($v_file);
				@gzclose($v_file_compressed);
			
				// ----- Check the minimum file size
				if (filesize($p_filename_compressed.'.gz') < 18)
				{
					return -2;
				}
	
				// ----- Extract the compressed attributes
				if (($v_file_compressed = @fopen($p_filename_compressed.'.gz', "rb")) == 0)
				{
					return -2;
				}
			
				// ----- Read the gzip file header
				$v_binary_data = @fread($v_file_compressed, 10);
				$v_data_header = unpack('a1id1/a1id2/a1cm/a1flag/Vmtime/a1xfl/a1os', $v_binary_data);
				
				$v_data_header['os'] = bin2hex($v_data_header['os']);
			
				// ----- Read the gzip file footer
				@fseek($v_file_compressed, filesize($p_filename_compressed.'.gz')-8);
				$v_binary_data = @fread($v_file_compressed, 8);
				$v_data_footer = unpack('Vcrc/Vcompressed_size', $v_binary_data);
			
				// ----- Set the attributes
				$p_header['compression'] = ord($v_data_header['cm']);
				//$p_header['mtime'] = $v_data_header['mtime'];
				$p_header['crc'] = $v_data_footer['crc'];
				$p_header['compressed_size'] = filesize($p_filename_compressed.'.gz')-18;
			
				// ----- Close the file
				@fclose($v_file_compressed);
			
				// ----- Call the header generation
				if (($v_result = $this->privWriteFileHeader($p_header)) != 1)
				{
					return $v_result;
				}
			
				// ----- Add the compressed data
				if (($v_file_compressed = @fopen($p_filename_compressed.'.gz', "rb")) == 0)
				{
					return -2;
				}
			
				// ----- Read the file by PCLZIP_READ_BLOCK_SIZE octets blocks
				fseek($v_file_compressed, 10);
				$v_size = $p_header['compressed_size'];
				while ($v_size != 0)
				{
					$v_read_size = ($v_size < PCLZIP_READ_BLOCK_SIZE ? $v_size : PCLZIP_READ_BLOCK_SIZE);
					$v_buffer = fread($v_file_compressed, $v_read_size);
					$v_binary_data = pack('a'.$v_read_size, $v_buffer);
					@fwrite($this->zip_fd, $v_binary_data, $v_read_size);
					$v_size -= $v_read_size;
				}
			
				// ----- Close the file
				@fclose($v_file_compressed);
			
				// ----- Unlink the temporary file
				@unlink($p_filename_compressed.'.gz');
			}
			
			// ----- Look for a directory
			else
			{
				// ----- Set the file properties
				$p_header['filename'] .= '/';
				$p_header['filename_len']++;
				$p_header['size'] = 0;
				$p_header['external'] = 0x41FF0010;   // Value for a folder : to be checked
				
				// ----- Call the header generation
				if (($v_result = $this->privWriteFileHeader($p_header)) != 1)
				{
					return $v_result;
				}
			}
		}		
		return $v_result;
	}


	function privWriteFileHeader(&$p_header)
	{
		$v_result=1;
		
		// TBC
		for(reset($p_header); $key = key($p_header); next($p_header))
		{
		}
		
		// ----- Store the offset position of the file
		$p_header['offset'] = ftell($this->zip_fd);
		
		// ----- Transform UNIX mtime to DOS format mdate/mtime
		$v_date = getdate($p_header['mtime']);
		$v_mtime = ($v_date['hours']<<11) + ($v_date['minutes']<<5) + $v_date['seconds']/2;
		$v_mdate = (($v_date['year']-1980)<<9) + ($v_date['mon']<<5) + $v_date['mday'];
		
		// ----- Packed data
		$v_binary_data = pack("VvvvvvVVVvv", 0x04034b50, $p_header['version'], $p_header['flag'],
		          $p_header['compression'], $v_mtime, $v_mdate,
		          $p_header['crc'], $p_header['compressed_size'], $p_header['size'],
		          strlen($p_header['stored_filename']), $p_header['extra_len']);
		
		// ----- Write the first 148 bytes of the header in the archive
		fputs($this->zip_fd, $v_binary_data, 30);
		
		// ----- Write the variable fields
		if (strlen($p_header['stored_filename']) != 0)
		{
			fputs($this->zip_fd, $p_header['stored_filename'], strlen($p_header['stored_filename']));
		}
		if ($p_header['extra_len'] != 0)
		{
			fputs($this->zip_fd, $p_header['extra'], $p_header['extra_len']);
		}
		
		// ----- return
		return $v_result;
	}

	function privWriteCentralFileHeader(&$p_header)
	{
		$v_result=1;
		
		// TBC
		for(reset($p_header); $key = key($p_header); next($p_header))
		{
		}
		
		// ----- Transform UNIX mtime to DOS format mdate/mtime
		if (isset($p_header['mtime'])) $v_date = getdate($p_header['mtime']);
		else $v_date = 0;
		
		$v_mtime = ($v_date['hours']<<11) + ($v_date['minutes']<<5) + $v_date['seconds']/2;
		$v_mdate = (($v_date['year']-1980)<<9) + ($v_date['mon']<<5) + $v_date['mday'];
		
		// ----- Packed data
		$v_binary_data = pack("VvvvvvvVVVvvvvvVV", 0x02014b50, $p_header['version'], $p_header['version_extracted'],
		  $p_header['flag'], $p_header['compression'], $v_mtime, $v_mdate, $p_header['crc'],
		  $p_header['compressed_size'], $p_header['size'],
		  strlen($p_header['stored_filename']), $p_header['extra_len'], $p_header['comment_len'],
		  $p_header['disk'], $p_header['internal'], $p_header['external'], $p_header['offset']);
		
		// ----- Write the 42 bytes of the header in the zip file
		fputs($this->zip_fd, $v_binary_data, 46);
		
		// ----- Write the variable fields
		if (strlen($p_header['stored_filename']) != 0)
		{
			fputs($this->zip_fd, $p_header['stored_filename'], strlen($p_header['stored_filename']));
		}
		if ($p_header['extra_len'] != 0)
		{
			fputs($this->zip_fd, $p_header['extra'], $p_header['extra_len']);
		}
		if ($p_header['comment_len'] != 0)
		{
			fputs($this->zip_fd, $p_header['comment'], $p_header['comment_len']);
		}
		
		// ----- return
		return $v_result;
	}
	

	function privConvertHeader2FileInfo($p_header, &$p_info)
	{
		$v_result=1;
		
		// ----- Get the interesting attributes
		$p_info['filename'] = $p_header['filename'];
		$p_info['stored_filename'] = $p_header['stored_filename'];
		$p_info['size'] = $p_header['size'];
		$p_info['compressed_size'] = $p_header['compressed_size'];
		$p_info['mtime'] = $p_header['mtime'];
		$p_info['comment'] = $p_header['comment'];
		$p_info['folder'] = ($p_header['external']==0x41FF0010);
		if (isset($p_header['index'])) $p_info['index'] = $p_header['index'];
		$p_info['status'] = $p_header['status'];
		
		// ----- return
		return $v_result;
	}

	function isAuthorized($filename)
	{
		global $system_zip_unauthorized;
		global $system_zip_unauthorizedpath;
		
		// get short name & full path
		if (!($pos = strrpos($filename,_PLOOPI_SEP)) === FALSE)
		{
			$fullpath = substr($filename,0,$pos+1);
			$short_filename = substr($filename,$pos+1);
		}
		else
		{
			$fullpath = '';
			$short_filename = $filename;
		}		
		
		$ret = TRUE;

		/*
		foreach($system_zip_unauthorizedpath as $key => $path)
		{
			if(stristr($fullpath,$path) == 0) $ret = FALSE; // path unauthorized
		}
		*/
		
		if (is_array($system_zip_unauthorized))
		{
			foreach($system_zip_unauthorized as $key => $ereg)
			{
				if(eregi($ereg, $short_filename)) $ret = FALSE; // filename unauthorized
			}
		}		
		//if (!$ret) echo " ERREUR";
		return($ret);
	}

}


function PclZipUtilPathReduction($p_dir)
{
	$v_result = "";
	
	// ----- Look for not empty path
	if ($p_dir != "")
	{
		// ----- Explode path by directory names
		$v_list = explode("/", $p_dir);
		
		// ----- Study directories from last to first
		for ($i=sizeof($v_list)-1; $i>=0; $i--)
		{
			// ----- Look for current path
			if ($v_list[$i] == ".")
			{
			}
			else if ($v_list[$i] == "..")
			{
				$i--;
			}
			else if (($v_list[$i] == "") && ($i!=(sizeof($v_list)-1)) && ($i!=0))
			{
			}
			else
			{
				$v_result = $v_list[$i].($i!=(sizeof($v_list)-1)?"/".$v_result:"");
			}
		}
	}
	
	return $v_result;
}

$archive  = './data/'._PLOOPI_DB_DATABASE.'.zip';

$zip = new zip($archive);
$zip->create("");

$list = array();
$list[0] = '.';

$list_result = $zip->add($list, '', '');

header("Content-type: application/octetstream");
header("Cache-control: private");
header("Pragma: public");
header("Expires: 0");
header("Location: $archive");
?>
