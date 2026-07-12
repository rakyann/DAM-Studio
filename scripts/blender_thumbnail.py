import bpy
import sys
import argparse

def render_thumbnail(input_path: str, output_path: str):
    bpy.ops.wm.read_factory_settings(use_empty=True)

    import mathutils

    # Import .glb
    bpy.ops.import_scene.gltf(filepath=input_path)

    # Frame camera mathematically without requiring view3d context
    objects = [obj for obj in bpy.context.scene.objects if obj.type == 'MESH']
    
    if objects:
        # Calculate bounding box
        bbox_corners = [obj.matrix_world @ mathutils.Vector(v) for obj in objects for v in obj.bound_box]
        min_v = mathutils.Vector((min(v.x for v in bbox_corners), min(v.y for v in bbox_corners), min(v.z for v in bbox_corners)))
        max_v = mathutils.Vector((max(v.x for v in bbox_corners), max(v.y for v in bbox_corners), max(v.z for v in bbox_corners)))
        center = (min_v + max_v) / 2.0
        radius = (max_v - min_v).length / 2.0
        
        # Add camera
        bpy.ops.object.camera_add(location=(center.x + radius * 1.5, center.y - radius * 1.5, center.z + radius))
        cam = bpy.context.object
        
        # Point camera to center
        direction = center - cam.location
        rot_quat = direction.to_track_quat('-Z', 'Y')
        cam.rotation_euler = rot_quat.to_euler()
    else:
        bpy.ops.object.camera_add(location=(3, -3, 2))
        cam = bpy.context.object

    bpy.context.scene.camera = cam

    # Setup lighting (3-point lighting)
    bpy.ops.object.light_add(type='SUN', location=(5, 5, 10))
    sun = bpy.context.object
    sun.data.energy = 2.0
    
    bpy.ops.object.light_add(type='AREA', location=(-5, -5, 5))
    fill = bpy.context.object
    fill.data.energy = 500.0
    
    bpy.ops.object.light_add(type='AREA', location=(0, 5, -5))
    rim = bpy.context.object
    rim.data.energy = 1000.0

    # Render settings optimized for weak VPS (CPU only)
    scene = bpy.context.scene
    
    # Force legacy EEVEE if possible, NEXT is too heavy for CPU
    try:
        scene.render.engine = 'BLENDER_EEVEE'
    except:
        scene.render.engine = 'BLENDER_EEVEE_NEXT'
        
    scene.render.resolution_x    = 600
    scene.render.resolution_y    = 450
    scene.render.image_settings.file_format = 'JPEG'
    scene.render.image_settings.quality = 85
    scene.render.filepath         = output_path
    
    # Low-end VPS specific settings
    try:
        scene.eevee.taa_render_samples = 16
        scene.eevee.use_shadows = False
        scene.eevee.use_volumetric = False
    except:
        pass

    bpy.ops.render.render(write_still=True)
    print(f"Thumbnail saved: {output_path}")

if __name__ == '__main__':
    argv = sys.argv
    argv = argv[argv.index('--') + 1:]

    parser = argparse.ArgumentParser()
    parser.add_argument('--input',  required=True)
    parser.add_argument('--output', required=True)
    args = parser.parse_args(argv)

    render_thumbnail(args.input, args.output)