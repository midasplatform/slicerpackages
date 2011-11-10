add_subdirectory( controllers )
add_subdirectory( models )

add_midas_style_test( StyleSlicerpackagesControllers ${CMAKE_SOURCE_DIR}/modules/slicerpackages/controllers )
add_midas_style_test( StyleSlicerpackagesModels ${CMAKE_SOURCE_DIR}/modules/slicerpackages/models )
