
class Etiqueta extends React.Component
{
  constructor(props) 
  {
    super(props)
    this.hijosid = 0
  }
  render()
    {
      return React.createElement('span', {key: 'etiqueta'+this.hijosid++, className: this.props.clase}, this.props.texto)
    }
}
class Titulo extends React.Component
{
  constructor(props) 
  {
    super(props)
    this.hijosid = 0
    this.tipo = 'h2'
    if(this.props.tipo == 'h1' || this.props.tipo == 'grande' || this.props.tipo == 'principal') this.tipo = 'h1'
    if(this.props.tipo == 'h3' || this.props.tipo == 'subtitulo' || this.props.tipo == 'secundario') this.tipo = 'h3'

  }
  render()
    {
      return React.createElement('div', {key: 'tit'+this.hijosid++}, 
              React.createElement(this.tipo, {key: 'tit'+this.hijosid++, className: this.props.clase}, this.props.texto))
    }
}
class Bolilla extends React.Component
{
  constructor(props) {
    super(props)
    this.hijosid = 0
  }
  render()
    {
      let b_bg = 'b_bg4'
      if(this.props.numero < 37) b_bg = 'b_bg3'
      if(this.props.numero < 25) b_bg = 'b_bg2'
      if(this.props.numero < 13) b_bg = 'b_bg1'

      return  React.createElement('div', {key: 'bolilla'+this.hijosid++, className: 'bolilla ' + b_bg}, 
              React.createElement('div', {key: 'bolilla'+this.hijosid++, className: 'bolilla_numero'}, 
              (this.props.numero < 10 ? '0' + this.props.numero : '' + this.props.numero)))
    }
}
class Contenedor extends React.Component
{

  constructor(props)
  {
    super(props)
    this.hijosid = 0
  }

  render()
  {
    return React.createElement('div', {key: 'c_'+this.hijosid++, className: this.props.clase}, this.props.contenido)
  }
}
class Sorteo extends React.Component
{
    constructor(props) 
    {
      super(props)
  
      this.handleClick = this.handleClick.bind(this)

      this.hijosid = 0
      this.mostrar = true

    }
  
    handleClick(e) 
    {
      this.mostrar = !this.mostrar
      console.info('click: sorteo mostrar ' + this.mostrar)
    }

    render()
    {
      let bolillas_oro = []
      let bolillas_revancha = []

      for(let b of this.props.sorteo.oro) bolillas_oro.push(React.createElement(Bolilla, {key: 'sorteo'+this.hijosid++, numero: b}))

      for(let b of this.props.sorteo.revancha) bolillas_revancha.push(React.createElement(Bolilla, {key: 'sorteo'+this.hijosid++, numero: b}))

      const etq_oro = React.createElement('div', {key: 'sorteo'+this.hijosid++, className: 'etq_bol'}, 'Oro:')
      const etq_revancha = React.createElement('div', {key: 'sorteo'+this.hijosid++, className: 'etq_bol'}, 'Revancha:')

      const extra_etq = React.createElement('div', {key: 'sorteo'+this.hijosid++, className: 'etq_bol'}, 'extra:')
      const extra_bol = React.createElement(Bolilla, {key: 'sorteo'+this.hijosid++, numero: this.props.sorteo.extra})

      const oro = React.createElement('div', {key: 'sorteo'+this.hijosid++, className: 'bolillas_oro'}, [bolillas_oro, extra_etq, extra_bol])
      const revancha = React.createElement('div', {key: 'sorteo'+this.hijosid++, className: 'bolillas_revancha'}, [bolillas_revancha])

      let _fecha = this.props.fecha.split('-')
      
      const fecha = React.createElement(Titulo, {key: 'sorteo'+this.hijosid++, tipo: 'h2', clase: '', texto: 'Sorteo del ' + _fecha[2] + '/' + _fecha[1] + '/' + _fecha[0]})

      const etq1 = React.createElement(Etiqueta, {key: 'sorteo'+this.hijosid++, clase: 'etq_bol', texto: 'Aciertos:'})
      const detalles1 = React.createElement('div', {key: 'sorteo'+this.hijosid++, className: 'detalles'}, [
          React.createElement('div', {key: 'sorteo'+this.hijosid++}, 'Oro: ' + this.props.sorteo.aciertos_oro + ' - '),
          React.createElement('div', {key: 'sorteo'+this.hijosid++}, 'Plata: ' + this.props.sorteo.aciertos_plata + ' - '),
          React.createElement('div', {key: 'sorteo'+this.hijosid++}, 'Revancha: ' + this.props.sorteo.aciertos_revancha)
      ])
      const etq2 = React.createElement(Etiqueta, {key: 'sorteo'+this.hijosid++, clase: 'etq_bol', texto: 'Montos:'})
      const detalles2 = React.createElement('div', {key: 'sorteo'+this.hijosid++, className: 'detalles'}, [
          React.createElement('div', {key: 'sorteo'+this.hijosid++}, 'Oro: ' + Intl.NumberFormat('es-UY',{style:'currency', currency:'UYU'}).format(this.props.sorteo.monto_oro) + ' - '),
          React.createElement('div', {key: 'sorteo'+this.hijosid++}, 'Plata: ' + Intl.NumberFormat('es-UY',{style:'currency', currency:'UYU'}).format(this.props.sorteo.monto_plata) + ' - '),
          React.createElement('div', {key: 'sorteo'+this.hijosid++}, 'Revancha: ' + Intl.NumberFormat('es-UY',{style:'currency', currency:'UYU'}).format(this.props.sorteo.monto_revancha))
      ])

      return React.createElement('div', {key: 'sorteo'+this.hijosid++, className: 'sorteo', onClick: this.handleClick}, [
        fecha, etq_oro, oro, etq_revancha, revancha, etq1, detalles1, etq2, detalles2
      ])
    }
}
class HistorialSorteos extends React.Component
{
  constructor(props)
  {
    super(props)
    this.hijosid = 0
  }
  render()
  {
    let sorteos = []
    for(s of this.props.sorteos)
		{
			sorteos.push(s)
		}
    return React.createElement(Contenedor, {key: 'hist'+this.hijosid++, clase: 'historial', contenido: sorteos}, null)
  }
}
class EstadisticasBolillas extends React.Component
{
    constructor(props) 
    {
      super(props)
      this.hijosid = 0
    }
  
    render()
    {
      let pares = []
      let ordenadas = []
      for(let b in this.props.bolillas)
      {
        ordenadas.push([b, this.props.bolillas[b]])
      }
      ordenadas.sort(function(a, b){ return a[1] - b[1]})

      for(let b of ordenadas)
      {
        pares.push(React.createElement(Contenedor, {key: 'hist'+this.hijosid++, clase: 'estadisticas_item', contenido: [
          React.createElement(Bolilla, {key: 'estad'+this.hijosid++, numero: b[0]}),
          React.createElement(Etiqueta, {key: 'estad'+this.hijosid++, clase: 'etiqueta', texto: '%' + b[1].toFixed(2)})
        ]}, null))
      }
      return React.createElement('div', {key: 'estad'+this.hijosid++, className: 'estadisticas'}, pares)
    }
}

class RecomendadasBolillas extends React.Component
{
    constructor(props) 
    {
      super(props)
      this.hijosid = 0
    }
  
    render()
    {
      let pares = []
      let ordenadas = this.props.bolillas
      ordenadas.sort()

      pares.push(React.createElement(Titulo, {key: 'estad'+this.hijosid++, clase: 'subtitulo', texto: 'Bolillas Recomendas para el proximo sorteo.'}))

      for(let b of ordenadas)
      {
        pares.push(React.createElement(Bolilla, {key: 'estad'+this.hijosid++, numero: b}))
      }
      return React.createElement('div', {key: 'estad'+this.hijosid++, className: 'recomendadas'}, pares)
    }
}

class InputForm extends React.Component 
{
  constructor(props) 
  {
    super(props)
    this.state = {value: ''+(this.props.value != undefined && this.props.value != null ? this.props.value : '')}

    this.handleChange = this.handleChange.bind(this)
  }

  handleChange(event) 
  {
    let valor = event.target.value
    if(this.props.filtros != undefined)
    {

    }
    this.setState({value: valor})
  }

  render() 
  {
    return React.createElement('div', {key: 'dir_form'+this.hijosid++, className: 'div_form'}, 
      [ 
        React.createElement(Etiqueta, {key: 'etq'+this.hijosid++, clase: 'etiqueta', texto: this.props.etiqueta, for: this.props.nombre}, null),
        React.createElement('input', {key: 'inpt'+this.hijosid++, name: this.props.nombre, type: this.props.tipo, value: this.state.value, onChange: this.handleChange}, null)
      ]
    )
  }
}
class SimpleForm extends React.Component 
{
  constructor(props) 
  {
    super(props)
    this.state = {msg: ''}

    this.handleSubmit = this.handleSubmit.bind(this)
  }

  handleSubmit(event) 
  {
    //event.preventDefault()
  }

  render() 
  {
    return React.createElement('form', {key: 'form'+this.hijosid++, accion: this.props.accion, method: this.props.tipo, onSubmit: this.handleSubmit}, this.props.campos)   
  }
}