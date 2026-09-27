import './bootstrap';
import { createIcons, House, ArrowLeftRight, Sparkles, CircleUserRound, Plus } from 'lucide';
import { ArcElement, Chart, DoughnutController, Tooltip } from 'chart.js';

createIcons({ icons: { House, ArrowLeftRight, Sparkles, CircleUserRound, Plus } });

Chart.register(ArcElement, DoughnutController, Tooltip);

const categoryChartData = document.getElementById('category-chart-data');
const categoryCanvas = document.getElementById('category-doughnut');

if (categoryChartData && categoryCanvas) {
	const categories = JSON.parse(categoryChartData.dataset.categories);
	const categoryColors = [
		'#11cec7',
		'#e87968',
		'#527aa5',
		'#d4a64a',
		'#65a982',
		'#8b79a8',
	];
	const currencyFormatter = new Intl.NumberFormat('es-AR', {
		style: 'currency',
		currency: 'ARS',
		maximumFractionDigits: 0,
	});

	document.querySelectorAll('#category-list .category-swatch').forEach((swatch, index) => {
		swatch.style.setProperty(
			'--category-color',
			categoryColors[index % categoryColors.length]
		);
	});

	new Chart(categoryCanvas, {
		type: 'doughnut',
		data: {
			labels: categories.map((category) => category.categoria),
			datasets: [{
				data: categories.map((category) => category.total),
				backgroundColor: categories.map((_, index) => (
					categoryColors[index % categoryColors.length]
				)),
				borderColor: '#ffffff',
				borderWidth: 3,
				borderRadius: 3,
				hoverOffset: 5,
			}],
		},
		options: {
			responsive: true,
			maintainAspectRatio: false,
			cutout: '72%',
			plugins: {
				legend: {
					display: false,
				},
				tooltip: {
					callbacks: {
						label(context) {
							const amount = context.parsed;
							const percentage = categories[context.dataIndex].porcentaje;

							return ` ${currencyFormatter.format(amount)} (${percentage.toLocaleString('es-AR')}%)`;
						},
					},
				},
			},
		},
	});
}
