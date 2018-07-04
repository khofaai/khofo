const __honey__ = {
	_rand(max) {
		return Math.floor(Math.random() * (max - 0)) + 0
	},
	generateHive() {
		var new_node = document.createElement('div');

		new_node.style.visibility = 'hidden';
		new_node.style.opacity = 0;
		new_node.style.height = 0;
		new_node.style.position = 'absolute';
		new_node.style.left = '-35848px';

		new_node.appendChild(__honey__.generatePot());
		new_node.appendChild(__honey__.generateLifeSpan());

		return new_node;
	},

	generatePot() {
		var pot_input = document.createElement('input');
		
		pot_input.id = pot_input.name = __honey__.bee();

		return pot_input;
	},

	generateLifeSpan() {
		var tmps_input = document.createElement('input');

		tmps_input.id = tmps_input.name = '_life_span_';
		tmps_input.value = Date.now();

		return tmps_input;
	},

	bee() {
	    var result;
	    var count = 0;
	    for (var prop in bees)
	        if (Math.random() < 1/++count)
	           result = prop;
	    return result;
	}
};


const bees = {
	formy_name		: 'formy_name',
	formy_job_name	: 'formy_job_name',
	formy_last_name	: 'formy_last_name',
	formy_first_name: 'formy_first_name',
	formy_email_name: 'formy_email_name',
};

export const honeyPot = function ( Vue ) {

	Vue.honey = {
		taste() {
			//  Check Taste 
			var tmps = document.getElementsByName('_life_span_')[0];

			return (Date.now() - tmps.value) > 5;
		}
	};

	Object.defineProperties(Vue.prototype,{
		$honey:{
			get:() => {
				return Vue.honey;
			}
		}
	})
};

export const honeyPotDirective = {
	bind:function(el,binding) {

		var inputs = el.getElementsByTagName('input');
		let rand_input = __honey__._rand(inputs.length);

		inputs[rand_input].parentNode.appendChild(__honey__.generateHive());
	}
};