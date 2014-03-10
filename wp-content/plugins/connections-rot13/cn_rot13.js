/**
 * Encypts or decrypts the suppplied string using ROT13 encryption
 * 
 * @author Steven A. Zahm
 */
cnROT13 = {
    writeROT13: function(anchorString) {
		document.write(anchorString.replace(/[a-zA-Z]/g, function(character){
			return String.fromCharCode( ( character <= 'Z' ? 90 : 122 ) >= ( character = character.charCodeAt(0) + 13 ) ? character : character - 26 );
		}));
	}
}