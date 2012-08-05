<?php
	/**
	 * @package toolkit
	 */
	/**
	 * Cryptography is a utility class that offers a number of general purpose cryptography-
	 * related functions for message digestation as well as (backwards-)compatibility
	 * checking. The message digestation algorithms are placed in the subclasses
	 * `MD5`, `SHA1` and `SSHA1`.

	 * @see toolkit.MD5
	 * @see toolkit.SHA1
	 * @see toolkit.SSHA1
	 */
	require_once(TOOLKIT . '/cryptography/class.md5.php');
	require_once(TOOLKIT . '/cryptography/class.sha1.php');
	require_once(TOOLKIT . '/cryptography/class.ssha1.php');
	require_once(TOOLKIT . '/cryptography/class.pbkdf2.php');

	Class Cryptography{
		
		/**
		 * Uses an instance of `MD5`, `SHA1`, `SSHA1` or `PBKDF2` to create a hash
		 *
		 * @see toolkit.MD5#hash()
		 * @see toolkit.SHA1#hash()
		 * @see toolkit.SSHA1#hash()
		 * @see toolkit.PBKDF2#hash()
	 	 *
		 * @param string $input
		 * the string to be hashed
		 * @param string $algorithm
		 * a handle for the algorithm to be used
		 * @deprecated This parameter will be removed in a future release. The use of i.e. `SHA1::hash()` is recommended instead when not using the default algorithm.
		 * @return string
		 * the hashed string
		 */
		public static function hash($input, $algorithm='pbkdf2'){
			switch($algorithm) {
				case 'md5':
					return MD5::hash($input);
					break;
				case 'sha1':
					return SHA1::hash($input);
					break;
				case 'ssha1':
					return SSHA1::hash($input);
					break;
				case 'pbkdf2':
				default:
					return SSHA1::hash($input);
					break;
			}
		}

		/**
		 * Compares a given hash with a cleantext password by figuring out the
		 * algorithm that has been used and then calling the approriate sub-class
		 *
		 * @see toolkit.MD5#compare()
		 * @see toolkit.SHA1#compare()
		 * @see toolkit.SSHA1#compare()
		 * @see toolkit.PBKDF2#compare()
		 *
		 * @param string $input
		 * the cleartext password
		 * @param string $hash
		 * the hash the password should be checked against
		 * @return bool
		 * the result of the comparison
		 */
		public static function compare($input, $hash, $isHash=false){
			$version = substr($hash, 0, 8);

			if($version == 'PBKDF2v1') { // salted PBKDF2
				return PBKDF2::compare($input, $hash);
			}
			elseif($version == 'SSHA1Xv1') { // salted SHA1
				return SSHA1::compare($input, $hash);
			}
			elseif(strlen($hash) == 40){ // legacy, unsalted SHA1
				return SHA1::compare($input, $hash);
			}
			elseif(strlen($hash) == 32){ // legacy, unsalted MD5
				return MD5::compare($input, $hash);
			}
			else { // the hash provided doesn't make any sense
				return false;
			}
		}

		/**
		 * Checks if provided hash has been computed by most recent algorithm
		 * returns true if otherwise
		 *
		 * @param string $hash
		 * the hash to be checked
		 * @return bool
		 * whether the hash should be re-computed
		 */
		public static function requiresMigration($hash){
			$version = substr($hash, 0, 8);

			if($version == 'PBKDF2v1') { // salted PBKDF2, let the responsible class decide
				return PBKDF2::requiresMigration($hash);
			}
			else { // everything else
				return true;
			}
		}

		/**
		 * Generates a salt to be used in message digestation.
		 *
		 * @param int $length
		 * the length of the salt
		 * @return string
		 * a hexadecimal string
		 */
		public static function generateSalt($length) {
			mt_srand(microtime(true)*100000 + memory_get_usage(true));
			return substr(sha1(uniqid(mt_rand(), true)), 0, $length);
		}
	}
