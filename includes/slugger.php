<?php

class slugger
{
	private static $alphabets = array(
									 'a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z',
									 'A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z',
									 '0','1','2','3','4','5','6','7','8','9'
									);
									
	private static $total_alphabets = 62;
	
	function shorten_injective($num)
	{
		$shorty  = "";
		
		if($num == 0)
			return $this->alphabets[0];
		
		while($num > 0)
		{
			$shorty .= $this->alphabets[$num % $this->total_alphabets];
			$num = intval($num / $this->total_alphabets);
		}
		
		return strrev($shorty);

	}

	function expand_surjective($alp)
	{
		$expn = 0;
		$char_arr = str_split($alp);
		$t = count($char_arr);
		foreach ($char_arr as $char)
		{
			$t--;
			$key = array_search($char, $this->alphabets);
			$expn += $key*pow($this->total_alphabets, $t); 
		}
		
		return $expn;
	}

	function sluggify($inp)
	{
		$inp = preg_replace("/\s\s+/", " ", $inp);
		$inp = preg_replace("/\s/", "-", $inp); //replace all spaces with dashes
		$inp = preg_replace("/[^a-zA-z0-9]/", "", $inp); //strip anything other than alphabets and numbers
		return $inp;
	}
	
	function randomize($length = 10) 
	{
		$n_a = self::$alphabets;
		$pass = '';
		
		for ($i = 0; $i < $length; $i++) 
		{
			shuffle($n_a);
			$key = array_rand($n_a);
			$pass .= $n_a[$key];
		}
		
		return $pass;
	}
	
}
?>
