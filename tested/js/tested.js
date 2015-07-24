/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */



/*
 *  code from http://bl.ocks.org/mbostock/1667367
 *  implementation of timeline and focus graph with D3.js framework
 *  next test should be with : http://code.shutterstock.com/rickshaw/
 */

/* 
 * SVG style container
 */

/*svg {
  font: 10px sans-serif;
}

path {
  fill: steelblue;
}

.axis path,
.axis line {
  fill: none;
  stroke: #000;
  shape-rendering: crispEdges;
}

.brush .extent {
  stroke: #fff;
  fill-opacity: .125;
  shape-rendering: crispEdges;
}
*/

/*
 * 
<body>
<script src="http://d3js.org/d3.v3.min.js"></script>
<script>
*/


var margin = {top: 10, right: 10, bottom: 100, left: 40},  //main window container margins 
    margin2 = {top: 430, right: 10, bottom: 20, left: 40}, //timeline window container margins
    width = 960 - margin.left - margin.right,              //main and timeline  window area
    height = 500 - margin.top - margin.bottom,
    height2 = 500 - margin2.top - margin2.bottom;

var parseDate = d3.time.format("%b %Y").parse;             // format date 

var x = d3.time.scale().range([0, width]),                 // scalers (x2,y2) timeline scale
    x2 = d3.time.scale().range([0, width]),
    y = d3.scale.linear().range([height, 0]),
    y2 = d3.scale.linear().range([height2, 0]);

var xAxis = d3.svg.axis().scale(x).orient("bottom"),       //axis creation
    xAxis2 = d3.svg.axis().scale(x2).orient("bottom"),
    yAxis = d3.svg.axis().scale(y).orient("left");

var brush = d3.svg.brush()                                 // brush selector 
    .x(x2)
    .on("brush", brushed);                                 // event entrypoint

var area = d3.svg.area()
    .interpolate("monotone")                               // main line/area drower 
    .x(function(d) { return x(d.date); })
    .y0(height)
    .y1(function(d) { return y(d.price); });

var area2 = d3.svg.area()                                 // timeline line/area drower
    .interpolate("monotone")
    .x(function(d) { return x2(d.date); })
    .y0(height2)
    .y1(function(d) { return y2(d.price); });

var svg = d3.select("body").append("svg")                //inserter into DOM 
    .attr("width", width + margin.left + margin.right)   // style container
    .attr("height", height + margin.top + margin.bottom);

svg.append("defs").append("clipPath")                   //create SVG elemets
    .attr("id", "clip")
  .append("rect")
    .attr("width", width)
    .attr("height", height);

var focus = svg.append("g")                            // SVG main window
    .attr("transform", "translate(" + margin.left + "," + margin.top + ")");

var context = svg.append("g")                          //SVG timeline window
    .attr("transform", "translate(" + margin2.left + "," + margin2.top + ")");
/*
 *    DATA LOADER
 */
d3.csv("sp500.csv", function(error, data) {

  data.forEach(function(d) {  // extract correct data 
    d.date = parseDate(d.date);
    d.price = +d.price;
  });

  x.domain(d3.extent(data.map(function(d) { return d.date; })));
  y.domain([0, d3.max(data.map(function(d) { return d.price; }))]);
  x2.domain(x.domain());
  y2.domain(y.domain());

  focus.append("path")
      .datum(data)
      .attr("clip-path", "url(#clip)")
      .attr("d", area);

  focus.append("g")
      .attr("class", "x axis")
      .attr("transform", "translate(0," + height + ")")
      .call(xAxis);

  focus.append("g")
      .attr("class", "y axis")
      .call(yAxis);

  context.append("path")
      .datum(data)
      .attr("d", area2);

  context.append("g")
      .attr("class", "x axis")
      .attr("transform", "translate(0," + height2 + ")")
      .call(xAxis2);

  context.append("g")
      .attr("class", "x brush")
      .call(brush)
    .selectAll("rect")
      .attr("y", -6)
      .attr("height", height2 + 7);
});

// end data loader

function brushed() {
  x.domain(brush.empty() ? x2.domain() : brush.extent());
  focus.select("path").attr("d", area);
  focus.select(".x.axis").call(xAxis);
}
