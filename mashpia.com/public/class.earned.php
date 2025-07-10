<?php
class Earned {
    protected $dates;

    public function __construct() {
        $this->dates = array(
			5769	=>	2454718, // Sep 8, 2008 
			5770	=>	2455082, // Sep 7, 2009 
			5771	=>	2455439, // Aug 30, 2010 
			5772	=>	2455810, // Sep 5, 2011 
			5773	=>	2456174, // Sep 3, 2012 
			5774	=>	2456531, // Aug 26, 2013
			5775	=>	2456908, // Sep 7, 2014
			5776	=>	2457265, // Aug 30, 2015
			5777	=>	2457636, // Sep 4, 2016
			5778	=>	2457885, // May 11, 2017 - was changed to 2457993 at some point but put back to original
			5779	=>	2458236, // April 27, 2018
			5780	=>	2458628, // May 24, 2019,
            5781    =>  2458983, // May 13, 2020
            5782    =>  2459363, // May 28, 2021
			5783	=>  2459718, // May 19, 2022
			5784	=>  2460073, // May 9, 2023
			5785	=>	2460456, // May 26, 2024
			5786	=>	2460812 // May 16, 2025
		);
    }

    public function getDates() {
        return $this->dates;
    }
}