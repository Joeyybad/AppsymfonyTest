const newTrend = document.querySelector('#newTrend')
const bestSales = document.querySelector('#bestSales')
const sales = document.querySelector('#sales')
const formContact = document.querySelector("#formContact")

const DATA_INFO = '/ajax/data'

formContact.addEventListener('submit', (e) => {
  e.preventDefault();
  fetch(DATA_INFO, {
    method: 'POST',
    body: new FormData(e.target)
  })
    .then(response => response.json())
    .then(handleData)

})

const handleData = function (data) {
  console.log(data)
}