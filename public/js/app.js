
function AjaxObjeto()
{
	var a = null
	const d = ["Msxml2.XMLHTTP","Microsoft.XMLHTTP"]
	if(window.ActiveXObject)
	{
		for(let e of d)
		{
			try
			{
				a = new ActiveXObject(e)
				break
			}
			catch(f){}
		}
	}
	else 
	{
		a = new XMLHttpRequest()
	}
	return a
}

function AjaxRequest(_exito,_fallo)
{
	var b = new AjaxObjeto()

	if(b != null)
	{
		b.onreadystatechange=function()
		{
			if(this.readyState == 4 && this.status == 200 && typeof _exito == "function")
			{
				_exito(this)
			}
			else if(this.readyState == 4 && this.status != 200)
			{
				if(typeof _fallo == "function")
				{
					_fallo(this)
				}
				else
				{
					console.error("Error en la respuesta ajax.")
					console.info(this.responseText)
				}
			}
		}
	}
	return b
}
function obtenerCSRF()
{
	const a = document.getElementById("csrf")
	if(a == undefined || a.trim() == '')
	{
		console.error("error: no hay csrf")
		return null
	}
	return a.value
}

window.addEventListener('load', function(e)
{
	let contenido = []
	let ukey = 0;

	console.log(datos);

	if(tipo == 'historial')
	{
		let sorteos = []
		for(s in datos)
		{
			sorteos.push(React.createElement(Sorteo, {key: ukey++, fecha: s, sorteo: datos[s]}, null))
		}
		contenido.push(React.createElement(HistorialSorteos, {key: ukey++, sorteos: sorteos}, null))
	}
	else if(tipo == 'estadisticas')
	{
		contenido.push(React.createElement(Titulo, {key: 'esta'+ukey++, tipo: 'h2', clase: 'etiqueta', texto: 'Oro'}))
		contenido.push(React.createElement(EstadisticasBolillas, {key: ukey++, bolillas: datos.oro}, null))
		contenido.push(React.createElement(Titulo, {key: 'esta'+ukey++, tipo: 'h2', clase: 'etiqueta', texto: 'Revancha'}))
		contenido.push(React.createElement(EstadisticasBolillas, {key: ukey++, bolillas: datos.revancha}, null))
	}
	else if(tipo == 'recomendadas')
	{
		contenido.push(React.createElement(RecomendadasBolillas, {key: ukey++, bolillas: datos}, null))
	}
	else if(tipo == 'aciertos')
	{
		let bols = React.createElement(InputForm, {key: ukey++, nombre: 'bolillas', tipo: 'text', value: (datos.bolillas != undefined ? datos.bolillas.join(' ') : ''), etiqueta: 'Números a verificar (separadas con espacios): '}, null)
		contenido.push(React.createElement(SimpleForm, {key: ukey++, tipo: 'post', accion: '/sorteos/aciertos', enctype: "multipart/form-data", campos: bols}, null))
		
		if(datos.oro != undefined)
		{
			contenido.push(React.createElement(Titulo, {key: 'aciertos'+ukey++, tipo: 'h1', clase: 'etiqueta', texto: 'Aciertos 5 de Oro'}))

			for(let aciertos = 5; aciertos > 1; aciertos--)
			{
				if(datos.oro[aciertos].length > 0)
				{
					contenido.push(React.createElement('hr', {key: 'linea'+ukey++}))
					contenido.push(React.createElement(Titulo, {key: 'aciertos'+ukey++, tipo: 'h2', clase: 'etiqueta', texto: aciertos + ' bolillas: '}))
					for(sorteo of datos.oro[aciertos])
					{
						contenido.push(React.createElement(Sorteo, {key: ukey++, fecha: sorteo.fecha.date.split(' ')[0], sorteo: sorteo}, null))
					}
				}			
			}	
		}
	}

	ReactDOM.render(
		React.createElement(Etiqueta, {key: 'msg1', clase: 'etiqueta', texto: msg}, null),
		document.getElementById('msg')
	);

	ReactDOM.render(
		contenido,
		document.getElementById('contenido')
	);
	
})