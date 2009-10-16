ClassGallerySlide = function(idUniq,withThumb) {
	
	this.arrGalleryUrl = new Array();
	
	this.iduniq = idUniq;
	this.index = 0;
	this.position = 0;
	this.withthumb = withThumb;
	this.maxindex = -1;
	this.maxposition = 0;
	
	/* 
	 * Ajout d'une image dans le tableau des url
	 */
	this.add_URL = function (id,url)
	{
		this.arrGalleryUrl[id] = url;
	    //les tableaux associatif ne reconnaissent pas la propriété length...
		this.maxindex++;
		this.maxposition = -1*(this.maxindex-4)*this.withthumb;
		// max position = -1 car decalage négatif
	    //              et (maxindex-4) car 2 premieres et 2 dernieres cases ne bougent pas donc on bouge de n-4 cases en tout 
	}
	
	/*
	 * ajout du libellé en dessous de la vignette 
	 */
	this.show_tooltip = function (myDivHover)
	{   
		idUniqLocal = this.iduniq;
		
	    $('gallerythumbs-'+idUniqLocal).childElements().each(
	        function(myDiv) {
	        	if(myDiv.tagName == "DIV" && myDiv.id.indexOf('thumb-') > -1) 
	        	{
		        	// Passage en 'hover' mais en concervant éventuellement le 'active'
		            if(myDiv.id == 'thumb-'+idUniqLocal+'-'+myDivHover)
		            {
		                $(myDiv.id).className = ($(myDiv.id).className.indexOf('active') > -1) ? 'active hover' : 'hover';
		            }
		            else
		            {
		                $(myDiv.id).className = ($(myDiv.id).className.indexOf('active') > -1) ? 'active' : '';
		            }
		        }
	        }
	    )
	}
	

	/*
	 * Ajout de la bordure de la vignette sélectionnée 
	 */
	this.show_border = function (myDivSelect)
	{
		idUniqLocal = this.iduniq;
		
	    $('gallerythumbs-'+this.iduniq).childElements().each(
	        function(myDiv) {
	        	if(myDiv.tagName == "DIV" && myDiv.id.indexOf('thumb-') > -1) 
	        	{
		            // Passage en 'active' mais en concervant éventuellement le 'hover'
		            if(myDiv.id == 'thumb-'+idUniqLocal+'-'+myDivSelect)
		            {
		                $(myDiv.id).className = ($(myDiv.id).className.indexOf('hover') > -1) ? 'active hover' : 'active';
		            }
		            else
		            {
		                $(myDiv.id).className = ($(myDiv.id).className.indexOf('hover') > -1) ? 'hover' : '';
		            }
	        	}
	        }
	        	
	    )
	}
	
	/*
	 * Clic direct sur une vignette en dessous 
	 */
	this.click_thumb = function (numThumb)
	{
		if(parseInt(numThumb,10) != this.index)
		{
			this.slide_thumbnail(numThumb);
		    $('galleryimage-'+this.iduniq).src = this.arrGalleryUrl[numThumb];
		    this.show_border(numThumb);
		}
	}

	/*
	 * Clic sur les flêches droite/gauche
	 */
	this.click_arrow = function (direction)
	{
	    var numThumb = this.index;

	    if((direction == '+') && (numThumb < this.maxindex))
	    	numThumb = numThumb + 1;
	    
	    if((direction == '-') && (numThumb > 0)) 
	    	numThumb = numThumb - 1;

	    numThumb = numThumb.toString();
	    if(numThumb.length < 2) numThumb = '0'+numThumb;

	    this.slide_thumbnail(numThumb);
	    $('galleryimage-'+this.iduniq).src = this.arrGalleryUrl[numThumb];
	    this.show_border(numThumb);
	}
	
	/*
	 * Systeme de déplacement de la barre de vignette
	 */
	this.slide_thumbnail = function(numThumb) 
	{
	    
	    var old_index = this.index;

	    this.index = parseInt(numThumb,10);

	    var move = 0;

	    if(this.index > old_index && this.index>=3) // on avance !
	    {
	        if(old_index<=2) old_index = 2; // On decale a cause des 2 premieres qui ne doivent pas bouger
	        move = -this.withthumb*(this.index-old_index);
	    }
	    
	    if(this.index < old_index && this.index<=(this.maxindex-3)) // on recule !
	    {
	        if(old_index>this.maxindex-2) old_index = this.maxindex-2; // On decale a cause des 2 dernieres qui ne doivent pas bouger
	        move = this.withthumb*(old_index-this.index);
	    }

	    this.position = this.position + move;
	    if((this.position) <= this.maxposition) this.position = this.maxposition;
	    if((this.position) > 0) this.position = 0;
	    
	    if(this.index > 0) $('arrowprev-'+this.iduniq).setStyle('display: block;'); else $('arrowprev-'+this.iduniq).setStyle('display: none;');
	    if(this.index < this.maxindex) $('arrownext-'+this.iduniq).setStyle('display: block;'); else $('arrownext-'+this.iduniq).setStyle('display: none;');

	    new Effect.Move($('gallerythumbs-'+this.iduniq), { x: this.position, y: 0, mode: 'absolute' });
	}

}
    



