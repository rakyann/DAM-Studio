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

    # Setup lighting
    bpy.ops.object.light_add(type='SUN', location=(5, 5, 10))
    bpy.ops.object.light_add(type='AREA', location=(-3, -3, 4))

    # Render settings
    scene = bpy.context.scene
    scene.render.engine           = 'CYCLES'
    scene.render.resolution_x    = 400
    scene.render.resolution_y    = 300
    scene.render.image_settings.file_format = 'JPEG'
    scene.render.filepath         = output_path
    scene.cycles.samples          = 32

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