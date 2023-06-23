const newTrend = document.querySelector('#newTrend')
const bestSales = document.querySelector('#bestSales')
const sales = document.querySelector('#sales')
const formContact = document.querySelector("#formContact")

formContact.addEventListener('submit', (e) => {
  e.preventDefault();
  const formData = new FormData(form);
})

const handleData = function (data) {
  console.log(data)
}