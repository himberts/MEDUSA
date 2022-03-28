<?php

if ( !isset( $GLOBALS[ "modulejson" ] ) || !is_array( $GLOBALS[ "modulejson" ] ) ) {
   $GLOBALS[ "modulejson" ] = [];
}

$GLOBALS[ "modulejson" ][ "mcsim" ] = json_decode( '{"executable":"mcsim.py","fields":[{"colspan":3,"default":"header3","id":"label_0","label":"McSim [<a target=_blank href=https://doi.org/10.1107/S1600576714013156>1,</a><a target=_blank href=http://scripts.iucr.org/cgi-bin/paper?S0021889890002801>2,</a><a target=_blank href=https://github.com/ehb54/GenApp-McSim>Source code</a>]","posthline":"true","prehline":"true","role":"input","type":"label"},{"default":0.001,"help":"q min, in inverse Angstroms","id":"qmin","label":"q min","role":"input","step":0.01,"type":"float"},{"default":1,"help":"q max, in inverse Angstrom","id":"qmax","label":"q max ","role":"input","step":0.001,"type":"float"},{"default":400,"help":"<p>Number of points in q.</p><p>Default: 100, Minimum 10, Maximum 2000</p>","id":"qpoints","label":"Number of points in q","max":2000,"min":10,"role":"input","type":"integer"},{"default":0,"help":"Relative polydispersity. Min: 0.0 (monodisperse), max: 0.2.","id":"polydispersity","label":"Relative polydispersity","max":0.2,"min":0,"role":"input","step":0.01,"type":"float"},{"default":0,"help":"<p>Volume fraction - for high concentration samples.</p><p> Giving rise to a hard sphere structure factor, S(q)</p><p>Min: 0.0 (no structure factor), max: 0.9.</p>","id":"eta","label":"Volume fraction","max":0.9,"min":0,"role":"input","step":0.01,"type":"float"},{"default":0,"help":"<p>Interface roughness, for non-sharp edges between models. </p> See Skar-Gislinge et al, PhysChemChemPhys 2011: Small-angle scattering from phospholipid nanodiscs: derivation and refinement of a molecular constrained analytical model form factor. </p><p> Decreasing scattering at high q, by I(q) = I(q)*exp(-(q*sigma_r)^2/2)</p><p>Min: 0.0 (no roughness), max: 10, default: none.</p>","id":"sigma_r","label":"Interface roughness, in Aangstrom","max":10,"min":0,"role":"input","step":0.01,"type":"float"},{"default":1,"help":"<p>Relative noise on simulated data.</p><p>Min: 0.0001, max: 10000.</p><p> the error is simulated using: sigma = noise*sqrt[(10000*I)/(0.9*q)], where I,q are calculated from the p(r)</p><p>Sedlak, Bruetzel and Lipfert (2017). J. Appl. Cryst. 50, 621-30. Quantitative evaluation of statistical errors in small- angle X-ray scattering measurements (https://doi.org/10.1107/S1600576717003077)</p>","id":"noise","label":"Relative noise","max":10000,"min":0.0001,"role":"input","step":0.01,"type":"float"},{"default":100,"help":"<p>Number of points in the estimated function p(r).</p><p>Default: 100, Minimum 10, Maximum 200</p>","id":"prpoints","label":"Number of points in p(r)","max":200,"min":10,"role":"input","type":"integer"},{"help":"<p>Exclude overlap regions.</p><p>If there is overlap with models higher up in the list, the points in the overlap region will be omitted.</p>","id":"exclude_overlap","label":"Exclude overlap regions","role":"input","type":"checkbox"},{"default":"Model","help":"<p>Models and parameters (in Angstrom):</p><p>Sphere; a: Radius, b,c: no effect</p><p> Ellipsoid; a, b, c: semiaxes</p><p> Cylinder/Disc; a, b: semiaxes, c: length</p><p>Cube; a: side length, b,c: no effect</p><p>Cuboid; a: width, b: depth, c: height</p><p>Hollow sphere; a: outer radius, b: inner radius, c: no effect</p><p>Hollow square; a: outer side length, b: inner side length, c: no effect </p><p>Cylindrical/discoidal ring; a: outer radius, b: inner radius, c: length </p>","id":"label_model","label":" ","norow":"true","readonly":"true","role":"input","size":17,"type":"text"},{"default":"a","help":"<p>Sphere: radius</p><p> Ellipsoid: semiaxis </p><p> Cylinder/Disc: semiaxis</p><p>Cube: side length</p><p>Cuboid: width</p><p>Hollow sphere: outer radius</p><p>Hollow square: outer side length</p><p>Cylindrical/discoidal ring: outer radius </p>","id":"label_a","label":" ","norow":"true","readonly":"true","role":"input","size":20,"type":"text"},{"default":"b","help":"<p>Sphere: no effect</p><p> Ellipsoid: semiaxis </p><p> Cylinder/Disc: semiaxis</p><p>Cube: no effect</p><p>Cuboid: depth</p><p>Hollow sphere: inner radius</p><p>Hollow square: inner side length</p><p>Cylindrical/discoidal ring: inner radius </p>","id":"label_b","label":" ","norow":"true","readonly":"true","role":"input","size":20,"type":"text"},{"default":"c","help":"<p>Sphere: no effect</p><p> Ellipsoid: semiaxis </p><p> Cylinder/Disc*: length</p><p>Cube: no effect</p><p>Cuboid: height</p><p>Hollow sphere: no effect </p><p>Hollow square: no effect</p><p>Cylindrical/discoidal ring*: length </p><p>*Cylinder and disc is the same model. They just differe in default paramters. Same is true for discoidal and cylindrical ring.</p>","id":"label_c","label":" ","norow":"true","readonly":"true","role":"input","size":20,"type":"text"},{"default":"Delta SLD","help":"Excess scattering length density of object","id":"label_p","label":" ","norow":"true","readonly":"true","role":"input","size":20,"type":"text"},{"default":"x_com","help":"center of mass x position of object","id":"label_x","label":" ","norow":"true","readonly":"true","role":"input","size":20,"type":"text"},{"default":"y_com","help":"center of mass y position of object","id":"label_y","label":" ","norow":"true","readonly":"true","role":"input","size":20,"type":"text"},{"default":"z_com","help":"center of mass z position of object","id":"label_z","label":" ","norow":"true","readonly":"true","role":"input","size":20,"type":"text"},{"default":"sphere","id":"model1","label":" ","norow":"true","role":"input","size":30,"type":"listbox","values":"Sphere~sphere~Ellipsoid (Tri-axial)~ellipsoid~Cylinder~cylinder~Disc~disc~Cube~cube~Cuboid~cuboid~Hollow sphere~hollow_sphere~Hollow cube~hollow_cube~Cylindrical ring~cyl_ring~Discoidal ring~disc_ring"},{"default":100,"id":"a1","label":" ","min":"0","norow":"true","required":"true","role":"input","type":"float"},{"id":"b1","label":" ","min":"0","norow":"true","role":"input","type":"float"},{"id":"c1","label":" ","min":"0","norow":"true","role":"input","type":"float"},{"default":1,"id":"p1","label":" ","norow":"true","role":"input","type":"float"},{"default":0,"id":"x1","label":" ","norow":"true","role":"input","type":"float"},{"default":0,"id":"y1","label":" ","norow":"true","role":"input","type":"float"},{"default":0,"id":"z1","label":" ","norow":"true","role":"input","type":"float"},{"default":"none","id":"model2","label":" ","norow":"true","role":"input","size":30,"type":"listbox","values":"Sphere~sphere~Ellipsoid (Tri-axial)~ellipsoid~Cylinder~cylinder~Disc~disc~Cube~cube~Cuboid~cuboid~Hollow sphere~hollow_sphere~Hollow cube~hollow_cube~Cylindrical ring~cyl_ring~Discoidal ring~disc_ring~Choose a model~none"},{"default":100,"id":"a2","label":" ","min":"0","norow":"true","required":"true","role":"input","type":"float"},{"id":"b2","label":" ","min":"0","norow":"true","role":"input","type":"float"},{"id":"c2","label":" ","min":"0","norow":"true","role":"input","type":"float"},{"default":1,"id":"p2","label":" ","norow":"true","role":"input","type":"float"},{"default":0,"id":"x2","label":" ","norow":"true","role":"input","type":"float"},{"default":0,"id":"y2","label":" ","norow":"true","role":"input","type":"float"},{"default":0,"id":"z2","label":" ","norow":"true","role":"input","type":"float"},{"default":"none","id":"model3","label":" ","norow":"true","role":"input","size":30,"type":"listbox","values":"Sphere~sphere~Ellipsoid (Tri-axial)~ellipsoid~Cylinder~cylinder~Disc~disc~Cube~cube~Cuboid~cuboid~Hollow sphere~hollow_sphere~Hollow cube~hollow_cube~Cylindrical ring~cyl_ring~Discoidal ring~disc_ring~Choose a model~none"},{"default":100,"id":"a3","label":" ","min":"0","norow":"true","required":"true","role":"input","type":"float"},{"id":"b3","label":" ","min":"0","norow":"true","role":"input","type":"float"},{"id":"c3","label":" ","min":"0","norow":"true","role":"input","type":"float"},{"default":1,"id":"p3","label":" ","norow":"true","role":"input","type":"float"},{"default":0,"id":"x3","label":" ","norow":"true","role":"input","type":"float"},{"default":0,"id":"y3","label":" ","norow":"true","role":"input","type":"float"},{"default":0,"id":"z3","label":" ","norow":"true","role":"input","type":"float"},{"default":"none","id":"model4","label":" ","norow":"true","role":"input","size":30,"type":"listbox","values":"Sphere~sphere~Ellipsoid (Tri-axial)~ellipsoid~Cylinder~cylinder~Disc~disc~Cube~cube~Cuboid~cuboid~Hollow sphere~hollow_sphere~Hollow cube~hollow_cube~Cylindrical ring~cyl_ring~Discoidal ring~disc_ring~Choose a model~none"},{"default":100,"id":"a4","label":" ","min":"0","norow":"true","required":"true","role":"input","type":"float"},{"id":"b4","label":" ","min":"0","norow":"true","role":"input","type":"float"},{"id":"c4","label":" ","min":"0","norow":"true","role":"input","type":"float"},{"default":1,"id":"p4","label":" ","norow":"true","role":"input","type":"float"},{"default":0,"id":"x4","label":" ","norow":"true","role":"input","type":"float"},{"default":0,"id":"y4","label":" ","norow":"true","role":"input","type":"float"},{"default":0,"id":"z4","label":" ","norow":"true","role":"input","type":"float"},{"default":"none","id":"model5","label":" ","norow":"true","role":"input","size":30,"type":"listbox","values":"Sphere~sphere~Ellipsoid (Tri-axial)~ellipsoid~Cylinder~cylinder~Disc~disc~Cube~cube~Cuboid~cuboid~Hollow sphere~hollow_sphere~Hollow cube~hollow_cube~Cylindrical ring~cyl_ring~Discoidal ring~disc_ring~Choose a model~none"},{"default":100,"id":"a5","label":" ","min":"0","norow":"true","required":"true","role":"input","type":"float"},{"id":"b5","label":" ","min":"0","norow":"true","role":"input","type":"float"},{"id":"c5","label":" ","min":"0","norow":"true","role":"input","type":"float"},{"default":1,"id":"p5","label":" ","norow":"true","role":"input","type":"float"},{"default":0,"id":"x5","label":" ","norow":"true","role":"input","type":"float"},{"default":0,"id":"y5","label":" ","norow":"true","role":"input","type":"float"},{"default":0,"id":"z5","label":" ","norow":"true","role":"input","type":"float"},{"colspan":2,"id":"hroutput","label":"<hr> Output files <hr>","role":"output","type":"label"},{"help":"The calculated p(r)","id":"pr","label":"p(r)","role":"output","type":"file"},{"help":"The calculated intensity, I(q)","id":"Iq","label":"Intensity, calculated (no errors)","role":"output","type":"file"},{"help":"<p>Simulated intensity, format: q, I(q), sigma.</p><p>sigma simulated using Sedlak et al (https://doi.org/10.1107/S1600576717003077)</p>","id":"Isim","label":"Intensity, simulated (with errors)","role":"output","type":"file"},{"help":"<p>PDB file of model, for visualization, e.g. in PyMOL or with online 3Dviewer.</p><p> All points represented as dummy Carbon beads (positive SLD), dummy Oxygen beads (negative SLD) or dummy Hydrogen beads (zero SLD).</p><p> WARNING: The model will not give correct scattering if used as input in, e.g., CRYSOL, PEPSI-SAXS, FOXS, CAPP, etc - it is only for vizualization</p>.","id":"pdb","label":"PDB file with model (open e.g. with PyMOL or <a target=_blank href=https://www.rcsb.org/3d-view>PDB-3Dviewer</a>)","role":"output","type":"file"},{"help":"Results packaged in a zip file","id":"zip","label":"Results zipped","role":"output","type":"file"},{"colspan":2,"id":"label_parameters","label":"<hr> Structural output parameters <hr>","role":"output","type":"label"},{"help":"Maximum distance in monodisperse particle","id":"Dmax","label":"Dmax, monodisperse","role":"output","type":"text"},{"help":"Radius of gyration of monodisperse particle","id":"Rg","label":"Rg, monodisperse","role":"output","type":"text"},{"help":"Maximum distance in polydisperse sample","id":"Dmax_poly","label":"Dmax, polydisperse","role":"output","type":"text"},{"help":"Radius of gyration of polydisperse sample","id":"Rg_poly","label":"Rg, polydisperse","role":"output","type":"text"},{"colspan":2,"help":"<p>Upper panel: Model(s) from different angles (red dots have positive SLD, green have negative SLD and grey have zero SLD). </p><p>Lower panel: p(r), I(q) on log-log and log-lin scale. </p>","id":"label_fig","label":"<p><hr> Plots of model, p(r) and scattering <hr></p><p>Upper panel: Model(s) from different angles (red dots have positive SLD, green have negative SLD and grey have zero SLD). </p><p>Lower panel: p(r), I(q) on log-log and log-lin scale. </p>","role":"output","type":"label"},{"id":"fig","label":"","role":"output","type":"image","width":"80%"}],"label":"McSim","moduleid":"mcsim","submitpolicy":"all"}' );
